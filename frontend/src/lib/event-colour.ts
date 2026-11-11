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
 * Where the 500 may sit. Below this, primary text sinks into the dark ground.
 * The top is the platform's own 500: white type on it is already only just
 * AA for a small label, so no Event may sit any lighter.
 */
const ANCHOR_LIGHTNESS = { min: 0.55, max: PLATFORM_RAMP[500].lightness };

/**
 * The most colourful a chosen 500 is taken to be, before each step is held
 * inside sRGB on its own (see inGamut()).
 */
const MAX_ANCHOR_CHROMA = 0.175;

/** How finely a step's chroma is searched down to the gamut edge. */
const GAMUT_SEARCH_STEPS = 20;

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

        scale[step] = inGamut({
            lightness: anchor + (platform.lightness - PLATFORM_RAMP[500].lightness),
            chroma: platform.chroma * colourfulness,
            hue: chosen.hue,
        });
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
    // Rounded down, so rounding can never carry a step back out of gamut.
    const chroma = Math.floor(colour.chroma * 1000) / 1000;

    return `oklch(${lightness}% ${chroma} ${round(colour.hue, 1)})`;
}

/**
 * A step with its chroma lowered, if it has to be, to the most that sRGB can
 * show at that lightness and hue. Left alone, the browser clips each channel
 * on its own, which moves the hue: a light blue tint comes out lilac. Found by
 * bisection, because the gamut edge has no closed form in OKLCH.
 */
function inGamut(colour: OklchColour): OklchColour {
    if (isDisplayable(colour)) {
        return colour;
    }

    let shown = 0;
    let clipped = colour.chroma;

    for (let attempt = 0; attempt < GAMUT_SEARCH_STEPS; attempt++) {
        const chroma = (shown + clipped) / 2;

        if (isDisplayable({ ...colour, chroma })) {
            shown = chroma;
        } else {
            clipped = chroma;
        }
    }

    return { ...colour, chroma: shown };
}

function isDisplayable(colour: OklchColour): boolean {
    return srgbOf(colour).every((channel) => channel >= 0 && channel <= 1);
}

/**
 * OKLCH to gamma-encoded sRGB, each channel 0 to 1 when the colour is
 * displayable and outside that range when it is not. Unclamped on purpose:
 * how far a channel overshoots is the question being asked.
 */
export function srgbOf(colour: OklchColour): [number, number, number] {
    const radians = colour.hue * Math.PI / 180;
    const a = colour.chroma * Math.cos(radians);
    const b = colour.chroma * Math.sin(radians);

    const l = (colour.lightness + 0.3963377774 * a + 0.2158037573 * b) ** 3;
    const m = (colour.lightness - 0.1055613458 * a - 0.0638541728 * b) ** 3;
    const s = (colour.lightness - 0.0894841775 * a - 1.291485548 * b) ** 3;

    return [
        4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s,
        -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s,
        -0.0041960863 * l - 0.7034186147 * m + 1.707614701 * s,
    ].map(encoded) as [number, number, number];
}

function encoded(channel: number): number {
    const magnitude = Math.abs(channel);
    const value = magnitude <= 0.0031308 ? magnitude * 12.92 : 1.055 * magnitude ** (1 / 2.4) - 0.055;

    return Math.sign(channel) * value;
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
