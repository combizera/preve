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

export type ColorUtility = 'bg' | 'text' | 'border' | 'ring';

const colorClassMap: Record<string, Record<ColorUtility, string>> = {
    red: {
        bg: 'bg-red-300 dark:bg-red-900',
        text: 'text-red-700 dark:text-red-200',
        border: 'border-red-400 dark:border-red-800',
        ring: 'ring-red-200 dark:ring-red-950',
    },
    blue: {
        bg: 'bg-blue-300 dark:bg-blue-900',
        text: 'text-blue-700 dark:text-blue-200',
        border: 'border-blue-400 dark:border-blue-800',
        ring: 'ring-blue-200 dark:ring-blue-950',
    },
    green: {
        bg: 'bg-green-300 dark:bg-green-900',
        text: 'text-green-700 dark:text-green-200',
        border: 'border-green-400 dark:border-green-800',
        ring: 'ring-green-200 dark:ring-green-950',
    },
    yellow: {
        bg: 'bg-yellow-300 dark:bg-yellow-900',
        text: 'text-yellow-700 dark:text-yellow-200',
        border: 'border-yellow-400 dark:border-yellow-800',
        ring: 'ring-yellow-200 dark:ring-yellow-950',
    },
    orange: {
        bg: 'bg-orange-300 dark:bg-orange-900',
        text: 'text-orange-700 dark:text-orange-200',
        border: 'border-orange-400 dark:border-orange-800',
        ring: 'ring-orange-200 dark:ring-orange-950',
    },
    purple: {
        bg: 'bg-purple-300 dark:bg-purple-900',
        text: 'text-purple-700 dark:text-purple-200',
        border: 'border-purple-400 dark:border-purple-800',
        ring: 'ring-purple-200 dark:ring-purple-950',
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
): string => {
    return colorClassMap[color][utility];
};

export const availableColors = Object.values(CategoryColor);
