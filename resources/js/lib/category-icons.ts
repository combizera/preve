import {
    Book,
    Briefcase,
    Car,
    Coffee,
    Dumbbell,
    Film,
    Gamepad2,
    Heart,
    House,
    Laptop,
    Music,
    Plane,
    ShoppingBag,
    ShoppingCart,
    Utensils,
} from 'lucide-vue-next';
import { Component } from 'vue';

/**
 * Enum for Category Icons
 * TODO: Aparentemente tem como criar o enum no backend e exportar para o frontend (ver como fazer)
 */
export enum CategoryIcon {
    House = 'house',
    ShoppingCart = 'shopping-cart',
    Car = 'car',
    Heart = 'heart',
    Coffee = 'coffee',
    Utensils = 'utensils',
    Plane = 'plane',
    Briefcase = 'briefcase',
    Gamepad2 = 'gamepad-2',
    Music = 'music',
    Book = 'book',
    Dumbbell = 'dumbbell',
    ShoppingBag = 'shopping-bag',
    Film = 'film',
    Laptop = 'laptop',
}

/**
 * String map for icon components
 */
const iconMap: Record<string, Component> = {
    [CategoryIcon.House]: House,
    [CategoryIcon.ShoppingCart]: ShoppingCart,
    [CategoryIcon.Car]: Car,
    [CategoryIcon.Heart]: Heart,
    [CategoryIcon.Coffee]: Coffee,
    [CategoryIcon.Utensils]: Utensils,
    [CategoryIcon.Plane]: Plane,
    [CategoryIcon.Briefcase]: Briefcase,
    [CategoryIcon.Gamepad2]: Gamepad2,
    [CategoryIcon.Music]: Music,
    [CategoryIcon.Book]: Book,
    [CategoryIcon.Dumbbell]: Dumbbell,
    [CategoryIcon.ShoppingBag]: ShoppingBag,
    [CategoryIcon.Film]: Film,
    [CategoryIcon.Laptop]: Laptop,
};

/**
 * Return the icon component based on the icon name
 * @param iconName - Icon Name (ex: 'house', 'shopping-cart')
 * @returns Vue Component
 */
export const getIconComponent = (iconName: string | null): Component => {
    return iconMap[iconName] || House;
};

/**
 * List of all available icons
 */
export const availableIcons = Object.values(CategoryIcon);
