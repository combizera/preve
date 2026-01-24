/**
 * Enum of category colors
 */
export enum CategoryColor {
    Red = 'red',
    Blue = 'blue',
    Green = 'green',
    Yellow = 'yellow',
    Orange = 'orange',
    Purple = 'purple',
}

export type ColorScale = 50 | 100 | 200 | 300 | 400 | 500 | 600 | 700 | 800 | 900 | 950;
export type ColorUtility = 'bg' | 'text' | 'border' | 'ring';

const colorClassMap: Record<string, Record<ColorScale, Record<ColorUtility, string>>> = {
    red: {
        50: { bg: 'bg-red-50', text: 'text-red-50', border: 'border-red-50', ring: 'ring-red-50' },
        100: { bg: 'bg-red-100', text: 'text-red-100', border: 'border-red-100', ring: 'ring-red-100' },
        200: { bg: 'bg-red-200', text: 'text-red-200', border: 'border-red-200', ring: 'ring-red-200' },
        300: { bg: 'bg-red-300', text: 'text-red-300', border: 'border-red-300', ring: 'ring-red-300' },
        400: { bg: 'bg-red-400', text: 'text-red-400', border: 'border-red-400', ring: 'ring-red-400' },
        500: { bg: 'bg-red-500', text: 'text-red-500', border: 'border-red-500', ring: 'ring-red-500' },
        600: { bg: 'bg-red-600', text: 'text-red-600', border: 'border-red-600', ring: 'ring-red-600' },
        700: { bg: 'bg-red-700', text: 'text-red-700', border: 'border-red-700', ring: 'ring-red-700' },
        800: { bg: 'bg-red-800', text: 'text-red-800', border: 'border-red-800', ring: 'ring-red-800' },
        900: { bg: 'bg-red-900', text: 'text-red-900', border: 'border-red-900', ring: 'ring-red-900' },
        950: { bg: 'bg-red-950', text: 'text-red-950', border: 'border-red-950', ring: 'ring-red-950' },
    },
    blue: {
        50: { bg: 'bg-blue-50', text: 'text-blue-50', border: 'border-blue-50', ring: 'ring-blue-50' },
        100: { bg: 'bg-blue-100', text: 'text-blue-100', border: 'border-blue-100', ring: 'ring-blue-100' },
        200: { bg: 'bg-blue-200', text: 'text-blue-200', border: 'border-blue-200', ring: 'ring-blue-200' },
        300: { bg: 'bg-blue-300', text: 'text-blue-300', border: 'border-blue-300', ring: 'ring-blue-300' },
        400: { bg: 'bg-blue-400', text: 'text-blue-400', border: 'border-blue-400', ring: 'ring-blue-400' },
        500: { bg: 'bg-blue-500', text: 'text-blue-500', border: 'border-blue-500', ring: 'ring-blue-500' },
        600: { bg: 'bg-blue-600', text: 'text-blue-600', border: 'border-blue-600', ring: 'ring-blue-600' },
        700: { bg: 'bg-blue-700', text: 'text-blue-700', border: 'border-blue-700', ring: 'ring-blue-700' },
        800: { bg: 'bg-blue-800', text: 'text-blue-800', border: 'border-blue-800', ring: 'ring-blue-800' },
        900: { bg: 'bg-blue-900', text: 'text-blue-900', border: 'border-blue-900', ring: 'ring-blue-900' },
        950: { bg: 'bg-blue-950', text: 'text-blue-950', border: 'border-blue-950', ring: 'ring-blue-950' },
    },
    green: {
        50: { bg: 'bg-green-50', text: 'text-green-50', border: 'border-green-50', ring: 'ring-green-50' },
        100: { bg: 'bg-green-100', text: 'text-green-100', border: 'border-green-100', ring: 'ring-green-100' },
        200: { bg: 'bg-green-200', text: 'text-green-200', border: 'border-green-200', ring: 'ring-green-200' },
        300: { bg: 'bg-green-300', text: 'text-green-300', border: 'border-green-300', ring: 'ring-green-300' },
        400: { bg: 'bg-green-400', text: 'text-green-400', border: 'border-green-400', ring: 'ring-green-400' },
        500: { bg: 'bg-green-500', text: 'text-green-500', border: 'border-green-500', ring: 'ring-green-500' },
        600: { bg: 'bg-green-600', text: 'text-green-600', border: 'border-green-600', ring: 'ring-green-600' },
        700: { bg: 'bg-green-700', text: 'text-green-700', border: 'border-green-700', ring: 'ring-green-700' },
        800: { bg: 'bg-green-800', text: 'text-green-800', border: 'border-green-800', ring: 'ring-green-800' },
        900: { bg: 'bg-green-900', text: 'text-green-900', border: 'border-green-900', ring: 'ring-green-900' },
        950: { bg: 'bg-green-950', text: 'text-green-950', border: 'border-green-950', ring: 'ring-green-950' },
    },
    yellow: {
        50: { bg: 'bg-yellow-50', text: 'text-yellow-50', border: 'border-yellow-50', ring: 'ring-yellow-50' },
        100: { bg: 'bg-yellow-100', text: 'text-yellow-100', border: 'border-yellow-100', ring: 'ring-yellow-100' },
        200: { bg: 'bg-yellow-200', text: 'text-yellow-200', border: 'border-yellow-200', ring: 'ring-yellow-200' },
        300: { bg: 'bg-yellow-300', text: 'text-yellow-300', border: 'border-yellow-300', ring: 'ring-yellow-300' },
        400: { bg: 'bg-yellow-400', text: 'text-yellow-400', border: 'border-yellow-400', ring: 'ring-yellow-400' },
        500: { bg: 'bg-yellow-500', text: 'text-yellow-500', border: 'border-yellow-500', ring: 'ring-yellow-500' },
        600: { bg: 'bg-yellow-600', text: 'text-yellow-600', border: 'border-yellow-600', ring: 'ring-yellow-600' },
        700: { bg: 'bg-yellow-700', text: 'text-yellow-700', border: 'border-yellow-700', ring: 'ring-yellow-700' },
        800: { bg: 'bg-yellow-800', text: 'text-yellow-800', border: 'border-yellow-800', ring: 'ring-yellow-800' },
        900: { bg: 'bg-yellow-900', text: 'text-yellow-900', border: 'border-yellow-900', ring: 'ring-yellow-900' },
        950: { bg: 'bg-yellow-950', text: 'text-yellow-950', border: 'border-yellow-950', ring: 'ring-yellow-950' },
    },
    orange: {
        50: { bg: 'bg-orange-50', text: 'text-orange-50', border: 'border-orange-50', ring: 'ring-orange-50' },
        100: { bg: 'bg-orange-100', text: 'text-orange-100', border: 'border-orange-100', ring: 'ring-orange-100' },
        200: { bg: 'bg-orange-200', text: 'text-orange-200', border: 'border-orange-200', ring: 'ring-orange-200' },
        300: { bg: 'bg-orange-300', text: 'text-orange-300', border: 'border-orange-300', ring: 'ring-orange-300' },
        400: { bg: 'bg-orange-400', text: 'text-orange-400', border: 'border-orange-400', ring: 'ring-orange-400' },
        500: { bg: 'bg-orange-500', text: 'text-orange-500', border: 'border-orange-500', ring: 'ring-orange-500' },
        600: { bg: 'bg-orange-600', text: 'text-orange-600', border: 'border-orange-600', ring: 'ring-orange-600' },
        700: { bg: 'bg-orange-700', text: 'text-orange-700', border: 'border-orange-700', ring: 'ring-orange-700' },
        800: { bg: 'bg-orange-800', text: 'text-orange-800', border: 'border-orange-800', ring: 'ring-orange-800' },
        900: { bg: 'bg-orange-900', text: 'text-orange-900', border: 'border-orange-900', ring: 'ring-orange-900' },
        950: { bg: 'bg-orange-950', text: 'text-orange-950', border: 'border-orange-950', ring: 'ring-orange-950' },
    },
    purple: {
        50: { bg: 'bg-purple-50', text: 'text-purple-50', border: 'border-purple-50', ring: 'ring-purple-50' },
        100: { bg: 'bg-purple-100', text: 'text-purple-100', border: 'border-purple-100', ring: 'ring-purple-100' },
        200: { bg: 'bg-purple-200', text: 'text-purple-200', border: 'border-purple-200', ring: 'ring-purple-200' },
        300: { bg: 'bg-purple-300', text: 'text-purple-300', border: 'border-purple-300', ring: 'ring-purple-300' },
        400: { bg: 'bg-purple-400', text: 'text-purple-400', border: 'border-purple-400', ring: 'ring-purple-400' },
        500: { bg: 'bg-purple-500', text: 'text-purple-500', border: 'border-purple-500', ring: 'ring-purple-500' },
        600: { bg: 'bg-purple-600', text: 'text-purple-600', border: 'border-purple-600', ring: 'ring-purple-600' },
        700: { bg: 'bg-purple-700', text: 'text-purple-700', border: 'border-purple-700', ring: 'ring-purple-700' },
        800: { bg: 'bg-purple-800', text: 'text-purple-800', border: 'border-purple-800', ring: 'ring-purple-800' },
        900: { bg: 'bg-purple-900', text: 'text-purple-900', border: 'border-purple-900', ring: 'ring-purple-900' },
        950: { bg: 'bg-purple-950', text: 'text-purple-950', border: 'border-purple-950', ring: 'ring-purple-950' },
    },
};

/**
 * Return Tailwind class for color, utility and scale
 * @param color
 * @param utility - Type of utilitary (ex: 'bg', 'text', 'border', 'ring')
 * @param scale
 */
export const getColorClass = (
    color: string,
    utility: ColorUtility = 'bg',
    scale: ColorScale = 500
): string => {
    return colorClassMap[color]?.[scale]?.[utility] || `${utility}-gray-500`;
};

export const availableColors = Object.values(CategoryColor);
