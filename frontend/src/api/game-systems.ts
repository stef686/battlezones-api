import type { ApiClient } from './client';

/** A game the platform knows, as a picker needs it. */
export interface GameSystem {
    id: number;
    name: string;
    slug: string;
}

/** Every Game System, in name order. No session needed. */
export function fetchGameSystems(client: ApiClient): Promise<GameSystem[]> {
    return client.get<{ data: GameSystem[] }>('/api/game-systems').then((response) => response.data);
}
