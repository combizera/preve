import type { CategoryColor } from '@/lib/category-colors'
import { CategoryIcon } from '@/lib/category-icons';
import type { TransactionType } from '@/types/models/transaction';

export interface ICategory {
    id: number;
    name: string;
    slug: string;
    type: TransactionType;
    description?: string;
    color: CategoryColor;
    icon: CategoryIcon;
    created_at: string;
    updated_at: string;
}

export type ICategoryForm = Pick<ICategory, 'name', 'description', 'type'> & {
    color?: CategoryColor;
    icon?: CategoryIcon;
};
