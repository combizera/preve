import { ICategory } from '@/types/models/category';
import { ICreditCard } from '@/types/models/credit-card';
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
    credit_card_id?: number | null;
    credit_card?: ICreditCard | null;
    parent_transaction_id?: string | null;
    split_number?: number | null;
    split_total?: number | null;
    amount: number;
    type: TransactionType;
    description: string;
    notes: string | null;
    transaction_date: string;
    purchase_date?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface ITransactionInput {
    id?: string;
    recurring_transaction_id?: string;
    category_id: number;
    tags: number[];
    savings_bucket_id?: number | null;
    credit_card_id?: number | null;
    splits?: number | null;
    amount: number;
    type: TransactionType;
    description: string;
    notes: string | null;
    transaction_date: string;
}
