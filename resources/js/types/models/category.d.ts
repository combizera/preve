import type { AccentColor } from '@/lib/accent-colors';
import { CategoryIcon } from '@/lib/category-icons';
import type { IForecastSeries } from '@/types/models/forecast';
import type { TransactionType } from '@/types/models/transaction';

export interface ICategory {
    id: number;
    name: string;
    slug: string;
    type: TransactionType;
    description?: string;
    color: AccentColor;
    icon: CategoryIcon;
    order: number;
    forecast_series?: IForecastSeries | null;
    created_at: string;
    updated_at: string;
}

export type ICategoryForm = Pick<ICategory, 'name', 'description', 'type'> & {
    color?: AccentColor;
    icon?: CategoryIcon;
};
