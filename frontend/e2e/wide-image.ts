import { deflateSync } from 'node:zlib';

/**
 * A wide PNG, built here rather than committed.
 *
 * The upload rules refuse anything under 1200x400, so the browser test needs a
 * real image of a real size. Generating one keeps a binary fixture out of the
 * repository — and out of every diff that touches this directory.
 */
export function wideImage(width = 1600, height = 600): Buffer {
    const raw = Buffer.alloc((width * 3 + 1) * height);

    for (let y = 0; y < height; y += 1) {
        const row = y * (width * 3 + 1);
        raw[row] = 0; // No per-row filter: the bytes are the pixels.

        for (let x = 0; x < width; x += 1) {
            const pixel = row + 1 + x * 3;
            raw[pixel] = 32;
            raw[pixel + 1] = 46;
            raw[pixel + 2] = 68;
        }
    }

    return Buffer.concat([
        Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
        chunk('IHDR', header(width, height)),
        chunk('IDAT', deflateSync(raw)),
        chunk('IEND', Buffer.alloc(0)),
    ]);
}

function header(width: number, height: number): Buffer {
    const bytes = Buffer.alloc(13);

    bytes.writeUInt32BE(width, 0);
    bytes.writeUInt32BE(height, 4);
    bytes[8] = 8; // Eight bits a channel.
    bytes[9] = 2; // Truecolour, no alpha.

    return bytes;
}

function chunk(type: string, data: Buffer): Buffer {
    const length = Buffer.alloc(4);
    length.writeUInt32BE(data.length, 0);

    const body = Buffer.concat([Buffer.from(type, 'ascii'), data]);
    const checksum = Buffer.alloc(4);
    checksum.writeUInt32BE(crc(body), 0);

    return Buffer.concat([length, body, checksum]);
}

const TABLE = Array.from({ length: 256 }, (_, index) => {
    let value = index;

    for (let bit = 0; bit < 8; bit += 1) {
        value = value & 1 ? 0xedb88320 ^ (value >>> 1) : value >>> 1;
    }

    return value >>> 0;
});

function crc(bytes: Buffer): number {
    let value = 0xffffffff;

    for (const byte of bytes) {
        value = (TABLE[(value ^ byte) & 0xff] as number) ^ (value >>> 8);
    }

    return (value ^ 0xffffffff) >>> 0;
}
