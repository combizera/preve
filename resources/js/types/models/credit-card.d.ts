import type { CreditCardColor } from '@/lib/credit-card-colors';

export interface ICreditCard {
    id: number;
    name: string;
    last_four: string | null;
    closing_day: number;
    due_day: number;
    color: CreditCardColor;
    credit_limit: number | null;
    current_invoice?: number;
    committed?: number;
    created_at: string;
    updated_at: string;
}

export interface ICreditCardSummary {
    committed: number;
    limit: number;
    available: number;
    invoice: number;
}

export interface IUpcomingInvoiceMonth {
    year: number;
    month: number;
    totals: Record<number, number>;
}

export type ICreditCardForm = {
    name: string;
    last_four: string | null;
    closing_day: number | null;
    due_day: number | null;
    color?: CreditCardColor;
    credit_limit: number | null;
};
