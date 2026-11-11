import { describe, expect, it } from 'vitest';

import {
    EVENT_COLOUR_PRESETS,
    EVENT_COLOUR_STEPS,
    eventColourScale,
    eventColourStyle,
    isEventColour,
    srgbOf,
} from '@/lib/event-colour';

describe('an event colour scale', () => {
    it('keeps the hue the organiser chose', () => {
        const blue = eventColourScale('#2563eb');
        const green = eventColourScale('#16a34a');

        expect(blue[500].hue).toBeCloseTo(262.9, 0);
        expect(green[500].hue).toBeCloseTo(149.2, 0);
    });

    it('runs from light to dark across every step', () => {
        const scale = eventColourScale('#c2410c');
        const lightness = EVENT_COLOUR_STEPS.map((step) => scale[step].lightness);

        expect(lightness).toEqual([...lightness].sort((a, b) => b - a));
        expect(new Set(lightness).size).toBe(EVENT_COLOUR_STEPS.length);
    });

    it('lifts a very dark colour so buttons and links still read on the dark ground', () => {
        const scale = eventColourScale('#0a0a1e');

        expect(scale[500].lightness).toBeGreaterThanOrEqual(0.55);
        expect(scale[600].lightness).toBeGreaterThanOrEqual(0.47);
    });

    it('darkens a very light colour so white text still reads on a button', () => {
        const scale = eventColourScale('#fdfcf0');

        expect(scale[500].lightness).toBeLessThanOrEqual(0.57);
        expect(scale[600].lightness).toBeLessThanOrEqual(0.5);
    });

    it('leaves a colour that already reads well where the organiser put it', () => {
        // The platform's own orange at 500, oklch(57% 0.14 55).
        expect(eventColourScale('#b8611f')[500].lightness).toBeCloseTo(0.57, 1);
    });

    it('never sits lighter at 500 than the platform does, so white button text keeps its contrast', () => {
        for (const hex of ['#fdfcf0', '#ffff00', '#00ffff', '#93c5fd']) {
            expect(eventColourScale(hex)[500].lightness).toBeLessThanOrEqual(0.57);
        }
    });

    it.each([
        ...EVENT_COLOUR_PRESETS.map((preset) => preset.hex),
        '#ffff00',
        '#00ffff',
        '#ff00ff',
        '#00ff00',
        '#0000ff',
        '#ff0000',
    ])('keeps every step of %s inside what a screen can show, so no step is clipped to another hue', (hex) => {
        const scale = eventColourScale(hex);

        for (const step of EVENT_COLOUR_STEPS) {
            for (const channel of srgbOf(scale[step])) {
                expect(channel).toBeGreaterThanOrEqual(-0.001);
                expect(channel).toBeLessThanOrEqual(1.001);
            }
        }
    });

    it('keeps as much of the chosen colour as fits, rather than greying it out', () => {
        expect(eventColourScale('#2563eb')[500].chroma).toBeGreaterThan(0.12);
    });
});

describe('an event colour on screen', () => {
    it('changes nothing for an event without one', () => {
        expect(eventColourStyle(null)).toEqual({});
    });

    it('replaces the primary ramp and the states drawn from it', () => {
        const style = eventColourStyle('#2563eb');

        for (const step of EVENT_COLOUR_STEPS) {
            expect(style[`--primary-${step}`]).toMatch(/^oklch\(/);
        }

        expect(style['--primary']).toBe('var(--primary-500)');
        expect(style['--primary-hover']).toBe('var(--primary-600)');
    });

    it('touches only primary, never the colours that carry meaning', () => {
        const names = Object.keys(eventColourStyle('#2563eb'));

        expect(names.every((name) => name.startsWith('--primary') || name.startsWith('--color-primary'))).toBe(true);
    });
});

describe('an event colour as typed', () => {
    it('is a lowercase six-digit hex, as the API stores it', () => {
        expect(isEventColour('#c2410c')).toBe(true);
        expect(isEventColour('#C2410C')).toBe(false);
        expect(isEventColour('#c40')).toBe(false);
        expect(isEventColour('c2410c')).toBe(false);
        expect(isEventColour('orange')).toBe(false);
    });
});
