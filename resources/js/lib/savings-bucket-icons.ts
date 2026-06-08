import {
    Baby,
    Briefcase,
    Car,
    Gift,
    GraduationCap,
    Hammer,
    Heart,
    Home,
    Laptop,
    PartyPopper,
    PawPrint,
    PiggyBank,
    Plane,
    Shield,
    Stethoscope,
    TrendingUp,
} from 'lucide-vue-next';
import { Component } from 'vue';

export enum SavingsBucketIcon {
    Shield = 'shield',
    PiggyBank = 'piggy-bank',
    Plane = 'plane',
    Home = 'home',
    Car = 'car',
    GraduationCap = 'graduation-cap',
    Baby = 'baby',
    Heart = 'heart',
    Stethoscope = 'stethoscope',
    TrendingUp = 'trending-up',
    Gift = 'gift',
    PawPrint = 'paw-print',
    Laptop = 'laptop',
    Hammer = 'hammer',
    PartyPopper = 'party-popper',
    Briefcase = 'briefcase',
}

const iconMap: Record<string, Component> = {
    [SavingsBucketIcon.Shield]: Shield,
    [SavingsBucketIcon.PiggyBank]: PiggyBank,
    [SavingsBucketIcon.Plane]: Plane,
    [SavingsBucketIcon.Home]: Home,
    [SavingsBucketIcon.Car]: Car,
    [SavingsBucketIcon.GraduationCap]: GraduationCap,
    [SavingsBucketIcon.Baby]: Baby,
    [SavingsBucketIcon.Heart]: Heart,
    [SavingsBucketIcon.Stethoscope]: Stethoscope,
    [SavingsBucketIcon.TrendingUp]: TrendingUp,
    [SavingsBucketIcon.Gift]: Gift,
    [SavingsBucketIcon.PawPrint]: PawPrint,
    [SavingsBucketIcon.Laptop]: Laptop,
    [SavingsBucketIcon.Hammer]: Hammer,
    [SavingsBucketIcon.PartyPopper]: PartyPopper,
    [SavingsBucketIcon.Briefcase]: Briefcase,
};

export const getSavingsBucketIconComponent = (
    iconName: string | null,
): Component => {
    if (!iconName) return PiggyBank;
    return iconMap[iconName] || PiggyBank;
};

export const availableSavingsBucketIcons = Object.values(SavingsBucketIcon);
