import { ICategory } from '@/types/models/category';
import { ITag } from '@/types/models/tag';
import type { TransactionType } from '@/types/models/transaction';

export type FrequencyType = 'monthly' | 'yearly';

export interface IRecurringTransaction {
    id?: number;
    category_id: number;
    category?: ICategory;
    tag_id?: number;
    tag?: ITag;
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
