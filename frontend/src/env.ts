/**
 * Everything the SPA needs to know about its API, baked at build time.
 *
 * Nothing secret belongs here: the bundle is public, so any value read from
 * `import.meta.env` is readable by anyone who loads the app.
 */
export interface Environment {
    apiUrl: string;
}

export function readEnvironment(env: Pick<ImportMetaEnv, 'VITE_API_URL'> = import.meta.env): Environment {
    const apiUrl = env.VITE_API_URL;

    if (!apiUrl) {
        throw new Error('VITE_API_URL is not set. Copy .env.example to .env and point it at the API.');
    }

    return { apiUrl: apiUrl.replace(/\/+$/, '') };
}
