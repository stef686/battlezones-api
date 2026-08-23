/**
 * Where the access token lives.
 *
 * Behind an interface because the native build stores it somewhere else:
 * @capacitor/preferences, and later secure storage (ADR-0001). Nothing above
 * this file should know that the web keeps it in localStorage.
 */
export interface TokenStorage {
    read(): string | null;
    write(token: string): void;
    clear(): void;
}

const STORAGE_KEY = 'battlezones.token';

/**
 * localStorage, defensively: a private window or blocked site data makes even
 * reading it throw, and a browser that cannot remember a token should still
 * render a login screen rather than a blank page.
 */
export class BrowserTokenStorage implements TokenStorage {
    read(): string | null {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch {
            return null;
        }
    }

    write(token: string): void {
        try {
            window.localStorage.setItem(STORAGE_KEY, token);
        } catch {
            // A session that cannot outlive the tab is still a usable session.
        }
    }

    clear(): void {
        try {
            window.localStorage.removeItem(STORAGE_KEY);
        } catch {
            // Nothing to clear if the store was never readable.
        }
    }
}

export class InMemoryTokenStorage implements TokenStorage {
    private token: string | null = null;

    read(): string | null {
        return this.token;
    }

    write(token: string): void {
        this.token = token;
    }

    clear(): void {
        this.token = null;
    }
}
