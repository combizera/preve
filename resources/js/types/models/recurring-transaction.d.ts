import { ICategory } from '@/types/models/category';
import { ICreditCard } from '@/types/models/credit-card';
import { ITag } from '@/types/models/tag';
import type { TransactionType } from '@/types/models/transaction';

export type FrequencyType = 'monthly' | 'yearly';

export interface IRecurringTransaction {
    id?: string;
    category_id: number;
    category?: ICategory;
    credit_card_id?: number | null;
    credit_card?: ICreditCard | null;
    tags?: ITag[];
    amount: number;
    frequency: FrequencyType;
    type: TransactionType;
    description: string;
    is_active: boolean;
    day_of_month: number;
    start_date: string;
    end_date?: string;
    created_at?: string;
    updated_at?: string;
}

export interface IRecurringTransactionInput {
    id?: string;
    category_id: number;
    credit_card_id?: number | null;
    tags: number[];
    amount: number;
    frequency: FrequencyType;
    type: TransactionType;
    description: string;
    is_active: boolean;
    day_of_month: number;
    start_date: string;
    end_date?: string;
}
