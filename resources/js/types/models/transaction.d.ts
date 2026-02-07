import { ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { ITag } from '@/types/models/tag';

export type TransactionType = 'income' | 'expense';

export interface ITransaction {
    id?: string;
    recurring_transaction_id?: number;
    recurring_transaction?: IRecurringTransaction;
    category_id?: number;
    category?: ICategory;
    tag_id?: number;
    tag?: ITag;
    amount: number;
    type: TransactionType;
    description: string;
    notes?: string;
    transaction_date: string;
    created_at?: string;
    updated_at?: string;
}
