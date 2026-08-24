/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_API_URL: string;
    /** False in dev and under test, where there is no service worker. */
    readonly PROD: boolean;
    readonly DEV: boolean;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}

/**
 * The registration helper vite-plugin-pwa generates into the build.
 *
 * Declared here rather than by referencing `vite-plugin-pwa/client`, whose
 * types pull in Workbox's, which are written against the WebWorker lib and do
 * not compile against a DOM-only project. Only the part this app uses is
 * described, which is also the part worth keeping honest.
 */
declare module 'virtual:pwa-register' {
    export interface RegisterSWOptions {
        immediate?: boolean;
        /** A new bundle is waiting for the reader to take it. */
        onNeedRefresh?: () => void;
        onOfflineReady?: () => void;
        onRegisteredSW?: (url: string, registration?: ServiceWorkerRegistration) => void;
        onRegisterError?: (error: unknown) => void;
    }

    export function registerSW(options?: RegisterSWOptions): (reloadPage?: boolean) => Promise<void>;
}
