import { ICategory } from '@/types/models/category';
import { ITag } from '@/types/models/tag';

export type TransactionType = 'income' | 'expense';

export interface ITransaction {
    id?: string;
    category_id: number;
    category?: ICategory;
    tag_id: number | null;
    tag?: ITag | null;
    amount: number;
    type: TransactionType;
    description: string;
    notes: string | null;
    transaction_date: string;
    created_at?: string;
    updated_at?: string;
}
