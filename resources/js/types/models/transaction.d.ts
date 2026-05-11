import { ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { ISavingsBucket } from '@/types/models/savings-bucket';
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
    savings_bucket_id?: number | null;
    savings_bucket?: ISavingsBucket | null;
    amount: number;
    type: TransactionType;
    description: string;
    notes: string | null;
    transaction_date: string;
    created_at?: string;
    updated_at?: string;
}

export interface ITransactionInput {
    id?: string;
    recurring_transaction_id?: string;
    category_id: number;
    tags: number[];
    savings_bucket_id?: number | null;
    amount: number;
    type: TransactionType;
    description: string;
    notes: string | null;
    transaction_date: string;
}
