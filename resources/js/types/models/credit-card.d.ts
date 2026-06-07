import type { AccentColor } from '@/lib/accent-colors';

export interface ICreditCard {
    id: number;
    name: string;
    last_four: string | null;
    closing_day: number;
    due_day: number;
    color: AccentColor;
    credit_limit: number | null;
    created_at: string;
    updated_at: string;
}

export type ICreditCardForm = {
    name: string;
    last_four: string | null;
    closing_day: number | null;
    due_day: number | null;
    color?: AccentColor;
    credit_limit: number | null;
};
