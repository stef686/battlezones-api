import { readEnvironment } from '@/env';

import { ApiError, normaliseNetworkFailure, normaliseResponse } from './errors';
import { BrowserTokenStorage, type TokenStorage } from './token-storage';

export interface Session {
    token: string;
    expiresAt: string | null;
}

export interface ApiClientOptions {
    baseUrl?: string;
    storage?: TokenStorage;
    /** Called when the session is gone for good, so the app can send the reader to login. */
    onSessionLost?: () => void;
    now?: () => number;
}

interface RequestOptions {
    body?: unknown;
    /** Set on the retry of a request that already refreshed, so it cannot loop. */
    retried?: boolean;
}

/** Refresh this far before expiry rather than waiting to be told we are late. */
const REFRESH_MARGIN_MS = 60_000;

export class ApiClient {
    private readonly baseUrl: string;
    private readonly storage: TokenStorage;
    private readonly onSessionLost: () => void;
    private readonly now: () => number;

    private expiresAt: number | null = null;

    /**
     * The single in-flight refresh.
     *
     * Every concurrent 401 waits on this same promise. Without it, a screen
     * firing three requests at once refreshes three times, and the second
     * refresh presents a token the first has already rotated away — logging
     * the reader out in the middle of a working session.
     */
    private refreshing: Promise<string | null> | null = null;

    constructor(options: ApiClientOptions = {}) {
        this.baseUrl = (options.baseUrl ?? readEnvironment().apiUrl).replace(/\/+$/, '');
        this.storage = options.storage ?? new BrowserTokenStorage();
        this.onSessionLost = options.onSessionLost ?? (() => {});
        this.now = options.now ?? (() => Date.now());
    }

    token(): string | null {
        return this.storage.read();
    }

    isAuthenticated(): boolean {
        return this.token() !== null;
    }

    setSession(session: Session): void {
        this.storage.write(session.token);
        this.expiresAt = session.expiresAt === null ? null : Date.parse(session.expiresAt);
    }

    clearSession(): void {
        this.storage.clear();
        this.expiresAt = null;
    }

    async login(email: string, password: string, deviceName: string): Promise<Session> {
        const response = await this.request<{ token: string; expires_at?: string | null }>(
            'POST',
            '/api/login/token',
            { body: { email, password, device_name: deviceName } },
        );

        const session = { token: response.token, expiresAt: response.expires_at ?? null };
        this.setSession(session);

        return session;
    }

    /**
     * Refresh before a request would have failed — on boot and on resume,
     * where the app has likely been asleep past the token's life.
     */
    async refreshIfDue(): Promise<void> {
        if (!this.isAuthenticated()) {
            return;
        }

        if (this.expiresAt !== null && this.expiresAt - this.now() > REFRESH_MARGIN_MS) {
            return;
        }

        await this.refresh();
    }

    /**
     * Exchange the current token for a new one, once, however many callers ask.
     */
    refresh(): Promise<string | null> {
        this.refreshing ??= this.performRefresh().finally(() => {
            this.refreshing = null;
        });

        return this.refreshing;
    }

    private async performRefresh(): Promise<string | null> {
        const token = this.token();

        if (token === null) {
            return null;
        }

        try {
            const response = await this.send('POST', '/api/auth/refresh', token, undefined);

            if (!response.ok) {
                this.loseSession();

                return null;
            }

            const body = (await response.json()) as { token: string; expires_at?: string | null };
            this.setSession({ token: body.token, expiresAt: body.expires_at ?? null });

            return body.token;
        } catch (cause) {
            // A refresh that never reached the API says nothing about the
            // session, so the token stays and the caller sees the network error.
            throw normaliseNetworkFailure(cause);
        }
    }

    /**
     * Idempotent: a failed refresh loses the session, and then the 401 that
     * triggered it falls through to the same conclusion. The reader should be
     * sent to login once, not once per request that was in flight.
     */
    private loseSession(): void {
        const hadSession = this.token() !== null;

        this.clearSession();

        if (hadSession) {
            this.onSessionLost();
        }
    }

    async request<T>(method: string, path: string, options: RequestOptions = {}): Promise<T> {
        let response: Response;

        try {
            response = await this.send(method, path, this.token(), options.body);
        } catch (cause) {
            throw normaliseNetworkFailure(cause);
        }

        if (response.status === 401 && !options.retried && this.isAuthenticated()) {
            const token = await this.refresh();

            if (token !== null) {
                return this.request<T>(method, path, { ...options, retried: true });
            }
        }

        const body = response.status === 204 ? null : await response.json().catch(() => null);

        if (!response.ok) {
            const error = normaliseResponse(response.status, body, response.headers);

            // A 401 that survived a refresh is a session that is genuinely
            // over. A 403 never is: the reader is signed in and simply not
            // allowed, and logging them out would be a lie.
            if (error.kind === 'unauthenticated') {
                this.loseSession();
            }

            throw error;
        }

        return body as T;
    }

    get<T>(path: string): Promise<T> {
        return this.request<T>('GET', path);
    }

    post<T>(path: string, body?: unknown): Promise<T> {
        return this.request<T>('POST', path, { body });
    }

    put<T>(path: string, body?: unknown): Promise<T> {
        return this.request<T>('PUT', path, { body });
    }

    patch<T>(path: string, body?: unknown): Promise<T> {
        return this.request<T>('PATCH', path, { body });
    }

    delete<T>(path: string): Promise<T> {
        return this.request<T>('DELETE', path);
    }

    private send(method: string, path: string, token: string | null, body: unknown): Promise<Response> {
        // A FormData body is handed over untouched: the browser writes the
        // multipart boundary into the Content-Type itself, and a header set
        // here would name a boundary the body does not use.
        const multipart = body instanceof FormData;

        return fetch(`${this.baseUrl}${path}`, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body === undefined || multipart ? {} : { 'Content-Type': 'application/json' }),
                ...(token === null ? {} : { Authorization: `Bearer ${token}` }),
            },
            body: body === undefined || multipart ? (body as FormData | undefined) : JSON.stringify(body),
        });
    }
}

export { ApiError };
export type { NormalisedError } from './errors';
