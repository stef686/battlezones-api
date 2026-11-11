/**
 * The Event Colour, turned into the primary ramp an Event's screens paint with.
 *
 * An Organiser chooses one `#rrggbb`; the API stores only that. The 50–950
 * scale is built here, in OKLCH, so each step is as light as the platform's
 * own step of the same name and only the hue and colourfulness are the
 * Organiser's. That is the clamp: whatever is chosen, the 500 a button is
 * filled with sits in the band where white type on it and it on the dark
 * ground both stay readable, and every other step keeps its distance from it.
 *
 * Only primary is replaced. Allegiance, success, warning and destructive keep
 * their own tokens, so an Event's look can never be mistaken for a state.
 */

export const EVENT_COLOUR_STEPS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950] as const;

export type EventColourStep = (typeof EVENT_COLOUR_STEPS)[number];

export interface OklchColour {
    /** 0 to 1. */
    lightness: number;
    chroma: number;
    /** Degrees, 0 to 360. */
    hue: number;
}

export type EventColourScale = Record<EventColourStep, OklchColour>;

/**
 * The platform ramp (styles/themes/battlezones.css), step by step: how light
 * each step is and how colourful, relative to its 500.
 */
const PLATFORM_RAMP: Record<EventColourStep, { lightness: number; chroma: number }> = {
    50: { lightness: 0.928, chroma: 0.08 },
    100: { lightness: 0.886, chroma: 0.1 },
    200: { lightness: 0.804, chroma: 0.12 },
    300: { lightness: 0.724, chroma: 0.14 },
    400: { lightness: 0.646, chroma: 0.16 },
    500: { lightness: 0.57, chroma: 0.14 },
    600: { lightness: 0.496, chroma: 0.12 },
    700: { lightness: 0.424, chroma: 0.1 },
    800: { lightness: 0.354, chroma: 0.08 },
    900: { lightness: 0.286, chroma: 0.06 },
    950: { lightness: 0.253, chroma: 0.05 },
};

/**
 * Where the 500 may sit. Below this, primary text sinks into the dark ground;
 * above it, white type on a primary button stops reading.
 */
const ANCHOR_LIGHTNESS = { min: 0.55, max: 0.6 };

/**
 * The most colourful a chosen 500 is taken to be. A fully saturated pick
 * would otherwise push the 400 past what most screens can show, and the
 * browser would clip it to a different hue.
 */
const MAX_ANCHOR_CHROMA = 0.175;

const HEX = /^#[0-9a-f]{6}$/;

/**
 * The colours offered with one tap. Any other is a hex away; these are only
 * the quick picks, spread round the wheel. None is a pure red, which reads as
 * a failure before it reads as an Event.
 */
export const EVENT_COLOUR_PRESETS: readonly { name: string; hex: string }[] = [
    { name: 'Ember', hex: '#c2410c' },
    { name: 'Gold', hex: '#b45309' },
    { name: 'Forest', hex: '#15803d' },
    { name: 'Teal', hex: '#0f766e' },
    { name: 'Azure', hex: '#2563eb' },
    { name: 'Indigo', hex: '#4f46e5' },
    { name: 'Violet', hex: '#7c3aed' },
    { name: 'Magenta', hex: '#be185d' },
];

/**
 * A hex as somebody might type it — either case, with or without the `#` —
 * as the API stores it, or null while it is not yet a whole colour.
 */
export function eventColourOf(typed: string): string | null {
    const trimmed = typed.trim().toLowerCase();
    const hex = trimmed.startsWith('#') ? trimmed : `#${trimmed}`;

    return isEventColour(hex) ? hex : null;
}

/** Whether a value is an Event Colour as the API stores it: lowercase `#rrggbb`. */
export function isEventColour(value: string): boolean {
    return HEX.test(value);
}

/** The full 50–950 scale for a chosen `#rrggbb`. */
export function eventColourScale(hex: string): EventColourScale {
    const chosen = oklchOf(hex);
    const anchor = clamp(chosen.lightness, ANCHOR_LIGHTNESS.min, ANCHOR_LIGHTNESS.max);
    const colourfulness = Math.min(chosen.chroma, MAX_ANCHOR_CHROMA) / PLATFORM_RAMP[500].chroma;

    const scale = {} as EventColourScale;

    for (const step of EVENT_COLOUR_STEPS) {
        const platform = PLATFORM_RAMP[step];

        scale[step] = {
            lightness: anchor + (platform.lightness - PLATFORM_RAMP[500].lightness),
            chroma: platform.chroma * colourfulness,
            hue: chosen.hue,
        };
    }

    return scale;
}

/**
 * The custom properties that put an Event Colour on everything beneath the
 * element wearing them. Empty for an Event without one, which leaves the
 * platform colour showing through.
 *
 * The states (`--primary`, `--primary-hover`, …) are redeclared too: they are
 * resolved where the theme declares them, so overriding the ramp alone would
 * leave every button the platform's colour. They follow the dark theme's
 * choice of steps.
 */
export function eventColourStyle(hex: string | null): Record<string, string> {
    if (hex === null || !isEventColour(hex)) {
        return {};
    }

    const scale = eventColourScale(hex);
    const style: Record<string, string> = {};

    for (const step of EVENT_COLOUR_STEPS) {
        style[`--primary-${step}`] = cssOf(scale[step]);
        style[`--color-primary-${step}`] = `var(--primary-${step})`;
    }

    return {
        ...style,
        '--primary': 'var(--primary-500)',
        '--primary-hover': 'var(--primary-600)',
        '--primary-focus': 'var(--primary-600)',
        '--primary-active': 'var(--primary-600)',
        '--primary-checked': 'var(--primary-500)',
        '--color-primary': 'var(--primary)',
        '--color-primary-hover': 'var(--primary-hover)',
        '--color-primary-focus': 'var(--primary-focus)',
        '--color-primary-active': 'var(--primary-active)',
        '--color-primary-checked': 'var(--primary-checked)',
    };
}

function cssOf(colour: OklchColour): string {
    const lightness = round(colour.lightness * 100, 1);

    return `oklch(${lightness}% ${round(colour.chroma, 3)} ${round(colour.hue, 1)})`;
}

/**
 * sRGB hex to OKLCH, by way of linear light and OKLab (Björn Ottosson's
 * matrices). A grey has no hue; it is given 0 so the scale stays a number.
 */
function oklchOf(hex: string): OklchColour {
    const [red, green, blue] = [1, 3, 5].map((offset) => linear(parseInt(hex.slice(offset, offset + 2), 16) / 255)) as [number, number, number];

    const l = Math.cbrt(0.4122214708 * red + 0.5363325363 * green + 0.0514459929 * blue);
    const m = Math.cbrt(0.2119034982 * red + 0.6806995451 * green + 0.1073969566 * blue);
    const s = Math.cbrt(0.0883024619 * red + 0.2817188376 * green + 0.6299787005 * blue);

    const lightness = 0.2104542553 * l + 0.793617785 * m - 0.0040720468 * s;
    const a = 1.9779984951 * l - 2.428592205 * m + 0.4505937099 * s;
    const b = 0.0259040371 * l + 0.7827717662 * m - 0.808675766 * s;

    const chroma = Math.hypot(a, b);
    const hue = chroma < 1e-4 ? 0 : (Math.atan2(b, a) * 180 / Math.PI + 360) % 360;

    return { lightness, chroma, hue };
}

function linear(channel: number): number {
    return channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4;
}

function clamp(value: number, min: number, max: number): number {
    return Math.min(max, Math.max(min, value));
}

function round(value: number, places: number): number {
    const factor = 10 ** places;

    return Math.round(value * factor) / factor;
}
