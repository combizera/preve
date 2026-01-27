import type { TransactionType } from '@/types/models/transaction';

export type CategoryColor =
    | 'red'
    | 'blue'
    | 'green'
    | 'yellow'
    | 'orange'
    | 'purple';

export interface ICategory {
    id: number;
    name: string;
    slug: string;
    type: TransactionType;
    description: string | null;
    color: CategoryColor;
    icon: string | null;
    created_at: string;
    updated_at: string;
}
