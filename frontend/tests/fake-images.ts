/**
 * Stand-ins for the browser's image decoding, which jsdom does not do.
 *
 * Installs an `Image` that reports a chosen size when its `src` is set, plus
 * object URLs. Whether jsdom offers those depends on the Node it runs on, so
 * a spec that picks a Banner must install these rather than rely on it. Each
 * object URL is unique, so a spec can tell the preview's URL from the one a
 * size check made and threw away. With
 * `hold()`, decodes wait for `finish()` instead, so a spec can decide the
 * order they settle in.
 */
import { vi } from 'vitest';

interface Decode {
    image: FakeImage;
    url: string;
}

let size: { width: number; height: number } | null = { width: 1600, height: 534 };
let held = false;
let pending: Decode[] = [];
let created = 0;

class FakeImage {
    naturalWidth = 0;
    naturalHeight = 0;
    onload: (() => void) | null = null;
    onerror: (() => void) | null = null;

    set src(url: string) {
        const decode = { image: this, url };

        if (held) {
            pending.push(decode);
        } else {
            queueMicrotask(() => settle(decode, size));
        }
    }
}

function settle(decode: Decode, measured: { width: number; height: number } | null): void {
    if (measured === null) {
        decode.image.onerror?.();

        return;
    }

    decode.image.naturalWidth = measured.width;
    decode.image.naturalHeight = measured.height;
    decode.image.onload?.();
}

/** Installs the fakes, every image measuring 1600×534 until told otherwise. */
export function fakeImages() {
    size = { width: 1600, height: 534 };
    held = false;
    pending = [];
    created = 0;

    // Stubbed rather than assigned, so `vi.unstubAllGlobals()` puts back
    // whatever the environment had, and no fake outlives the spec.
    vi.stubGlobal('Image', FakeImage);
    vi.stubGlobal('URL', class extends URL {
        static override createObjectURL = vi.fn(() => `blob:image-${++created}`);
        static override revokeObjectURL = vi.fn();
    });

    return {
        /** Every image decoded from now on measures this. */
        measure(width: number, height: number): void {
            size = { width, height };
        },
        /** Every image decoded from now on fails to decode. */
        failToDecode(): void {
            size = null;
        },
        /** Decodes wait for finish() rather than settling on their own. */
        hold(): void {
            held = true;
        },
        /** Settles the decode started in this position, at this size. */
        finish(position: number, width: number, height: number): void {
            const decode = pending[position];

            if (decode !== undefined) {
                settle(decode, { width, height });
            }
        },
    };
}
