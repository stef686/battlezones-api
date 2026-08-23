import { readEnvironment } from '@/env';

import type { paths } from './schema';

export type ApiPaths = paths;

export class ApiError extends Error {
    constructor(
        public readonly status: number,
        public readonly body: unknown,
    ) {
        super(`The API responded with ${status}.`);
        this.name = 'ApiError';
    }
}

/**
 * A thin fetch wrapper over the API.
 *
 * Authentication is a bearer token on every request (see ADR-0001), so the
 * token is passed in rather than read from storage here: where it is kept
 * differs between web and the Capacitor build.
 */
export class ApiClient {
    private readonly baseUrl: string;

    constructor(
        private readonly token: () => string | null = () => null,
        baseUrl: string = readEnvironment().apiUrl,
    ) {
        this.baseUrl = baseUrl;
    }

    async request<T>(method: string, path: string, body?: unknown): Promise<T> {
        const token = this.token();

        const response = await fetch(`${this.baseUrl}${path}`, {
            method,
            headers: {
                Accept: 'application/json',
                ...(body === undefined ? {} : { 'Content-Type': 'application/json' }),
                ...(token === null ? {} : { Authorization: `Bearer ${token}` }),
            },
            body: body === undefined ? undefined : JSON.stringify(body),
        });

        const payload = response.status === 204 ? null : await response.json().catch(() => null);

        if (!response.ok) {
            throw new ApiError(response.status, payload);
        }

        return payload as T;
    }

    get<T>(path: string): Promise<T> {
        return this.request<T>('GET', path);
    }

    post<T>(path: string, body?: unknown): Promise<T> {
        return this.request<T>('POST', path, body);
    }

    put<T>(path: string, body?: unknown): Promise<T> {
        return this.request<T>('PUT', path, body);
    }

    patch<T>(path: string, body?: unknown): Promise<T> {
        return this.request<T>('PATCH', path, body);
    }

    delete<T>(path: string): Promise<T> {
        return this.request<T>('DELETE', path);
    }
}
