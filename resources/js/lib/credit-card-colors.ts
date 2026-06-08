import {
    AccentColor,
    type ColorUtility,
    getColorClass,
} from '@/lib/accent-colors';

export type CreditCardColor = AccentColor | 'black';

const blackClassMap: Record<ColorUtility, string> = {
    bg: 'bg-neutral-800 dark:bg-neutral-900',
    picker: 'bg-neutral-900 dark:bg-black',
    text: 'text-neutral-100',
    border: 'border-neutral-700',
    ring: 'ring-neutral-700',
};

/**
 * Resolve a Tailwind class for a credit card color. Behaves like
 * getColorClass for the shared accent palette, plus a dedicated 'black' style
 * that the global AccentColor palette intentionally does not expose.
 */
export const getCreditCardColorClass = (
    color: CreditCardColor,
    utility: ColorUtility = 'bg',
): string =>
    color === 'black' ? blackClassMap[utility] : getColorClass(color, utility);

export const availableCreditCardColors: CreditCardColor[] = [
    ...Object.values(AccentColor),
    'black',
];

const chartHexMap: Record<CreditCardColor, string> = {
    red: '#ef4444',
    orange: '#f97316',
    amber: '#f59e0b',
    yellow: '#eab308',
    lime: '#84cc16',
    green: '#22c55e',
    emerald: '#10b981',
    teal: '#14b8a6',
    cyan: '#06b6d4',
    sky: '#0ea5e9',
    blue: '#3b82f6',
    indigo: '#6366f1',
    violet: '#8b5cf6',
    purple: '#a855f7',
    fuchsia: '#d946ef',
    pink: '#ec4899',
    black: '#171717',
};

/**
 * Raw hex color for a card, used by charts (Unovis needs concrete color values,
 * not Tailwind utility classes).
 */
export const getCreditCardChartColor = (color: CreditCardColor): string =>
    chartHexMap[color];
