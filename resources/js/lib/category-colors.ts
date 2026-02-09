/**
 * Enum of category colors
 */
export enum CategoryColor {
    Red = 'red',
    Orange = 'orange',
    Amber = 'amber',
    Yellow = 'yellow',
    Lime = 'lime',
    Green = 'green',
    Emerald = 'emerald',
    Teal = 'teal',
    Cyan = 'cyan',
    Sky = 'sky',
    Blue = 'blue',
    Indigo = 'indigo',
    Violet = 'violet',
    Purple = 'purple',
    Fuchsia = 'fuchsia',
    Pink = 'pink',
}

export type ColorUtility = 'bg' | 'text' | 'border' | 'ring' | 'picker';

const colorClassMap: Record<string, Record<ColorUtility, string>> = {
    red: {
        bg: 'bg-red-200 dark:bg-red-900',
        picker: 'bg-red-500 dark:bg-red-600',
        text: 'text-red-500 dark:text-red-300',
        border: 'border-red-300 dark:border-red-800',
        ring: 'ring-red-400 dark:ring-red-950',
    },
    orange: {
        bg: 'bg-orange-200 dark:bg-orange-900',
        picker: 'bg-orange-500 dark:bg-orange-600',
        text: 'text-orange-500 dark:text-orange-300',
        border: 'border-orange-300 dark:border-orange-800',
        ring: 'ring-orange-400 dark:ring-orange-950',
    },
    amber: {
        bg: 'bg-amber-200 dark:bg-amber-900',
        picker: 'bg-amber-500 dark:bg-amber-600',
        text: 'text-amber-500 dark:text-amber-300',
        border: 'border-amber-300 dark:border-amber-800',
        ring: 'ring-amber-400 dark:ring-amber-950',
    },
    yellow: {
        bg: 'bg-yellow-200 dark:bg-yellow-900',
        picker: 'bg-yellow-500 dark:bg-yellow-600',
        text: 'text-yellow-500 dark:text-yellow-300',
        border: 'border-yellow-300 dark:border-yellow-800',
        ring: 'ring-yellow-400 dark:ring-yellow-950',
    },
    lime: {
        bg: 'bg-lime-200 dark:bg-lime-900',
        picker: 'bg-lime-500 dark:bg-lime-600',
        text: 'text-lime-500 dark:text-lime-300',
        border: 'border-lime-300 dark:border-lime-800',
        ring: 'ring-lime-400 dark:ring-lime-950',
    },
    green: {
        bg: 'bg-green-200 dark:bg-green-900',
        picker: 'bg-green-500 dark:bg-green-600',
        text: 'text-green-500 dark:text-green-300',
        border: 'border-green-300 dark:border-green-800',
        ring: 'ring-green-400 dark:ring-green-950',
    },
    emerald: {
        bg: 'bg-emerald-200 dark:bg-emerald-900',
        picker: 'bg-emerald-500 dark:bg-emerald-600',
        text: 'text-emerald-500 dark:text-emerald-300',
        border: 'border-emerald-300 dark:border-emerald-800',
        ring: 'ring-emerald-400 dark:ring-emerald-950',
    },
    teal: {
        bg: 'bg-teal-200 dark:bg-teal-900',
        picker: 'bg-teal-500 dark:bg-teal-600',
        text: 'text-teal-500 dark:text-teal-300',
        border: 'border-teal-300 dark:border-teal-800',
        ring: 'ring-teal-400 dark:ring-teal-950',
    },
    cyan: {
        bg: 'bg-cyan-200 dark:bg-cyan-900',
        picker: 'bg-cyan-500 dark:bg-cyan-600',
        text: 'text-cyan-500 dark:text-cyan-300',
        border: 'border-cyan-300 dark:border-cyan-800',
        ring: 'ring-cyan-400 dark:ring-cyan-950',
    },
    sky: {
        bg: 'bg-sky-200 dark:bg-sky-900',
        picker: 'bg-sky-500 dark:bg-sky-600',
        text: 'text-sky-500 dark:text-sky-300',
        border: 'border-sky-300 dark:border-sky-800',
        ring: 'ring-sky-400 dark:ring-sky-950',
    },
    blue: {
        bg: 'bg-blue-200 dark:bg-blue-900',
        picker: 'bg-blue-500 dark:bg-blue-600',
        text: 'text-blue-500 dark:text-blue-300',
        border: 'border-blue-300 dark:border-blue-800',
        ring: 'ring-blue-400 dark:ring-blue-950',
    },
    indigo: {
        bg: 'bg-indigo-200 dark:bg-indigo-900',
        picker: 'bg-indigo-500 dark:bg-indigo-600',
        text: 'text-indigo-500 dark:text-indigo-300',
        border: 'border-indigo-300 dark:border-indigo-800',
        ring: 'ring-indigo-400 dark:ring-indigo-950',
    },
    violet: {
        bg: 'bg-violet-200 dark:bg-violet-900',
        picker: 'bg-violet-500 dark:bg-violet-600',
        text: 'text-violet-500 dark:text-violet-300',
        border: 'border-violet-300 dark:border-violet-800',
        ring: 'ring-violet-400 dark:ring-violet-950',
    },
    purple: {
        bg: 'bg-purple-200 dark:bg-purple-900',
        picker: 'bg-purple-500 dark:bg-purple-600',
        text: 'text-purple-500 dark:text-purple-300',
        border: 'border-purple-300 dark:border-purple-800',
        ring: 'ring-purple-400 dark:ring-purple-950',
    },
    fuchsia: {
        bg: 'bg-fuchsia-200 dark:bg-fuchsia-900',
        picker: 'bg-fuchsia-500 dark:bg-fuchsia-600',
        text: 'text-fuchsia-500 dark:text-fuchsia-300',
        border: 'border-fuchsia-300 dark:border-fuchsia-800',
        ring: 'ring-fuchsia-400 dark:ring-fuchsia-950',
    },
    pink: {
        bg: 'bg-pink-200 dark:bg-pink-900',
        picker: 'bg-pink-500 dark:bg-pink-600',
        text: 'text-pink-500 dark:text-pink-300',
        border: 'border-pink-300 dark:border-pink-800',
        ring: 'ring-pink-400 dark:ring-pink-950',
    },
};

/**
 * Return Tailwind class for color, utility and scale
 * @param color
 * @param utility - Type of utility (ex: 'bg', 'text', 'border', 'ring')
 */
export const getColorClass = (
    color: CategoryColor,
    utility: ColorUtility = 'bg',
): string => {
    return colorClassMap[color][utility];
};

export const availableColors = Object.values(CategoryColor);
