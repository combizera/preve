import { ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { ITag } from '@/types/models/tag';

export type TransactionType = 'income' | 'expense';

export interface IDailyBalance {
    day: number;
    amount: number;
}

export interface ITransaction {
    id?: string;
    recurring_transaction_id?: string;
    recurring_transaction?: IRecurringTransaction;
    category_id?: number;
    category?: ICategory;
    tags?: ITag[];
    amount: number;
    type: TransactionType;
    description: string;
    notes?: string;
    transaction_date: string;
    created_at?: string;
    updated_at?: string;
}

export interface ITransactionInput {
    id?: string;
    recurring_transaction_id?: string;
    category_id: number;
    tags: number[];
    amount: number;
    type: TransactionType;
    description: string;
    notes?: string;
    transaction_date: string;
}
