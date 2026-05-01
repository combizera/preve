import type { ICategory } from '@/types/models/category';

export type ForecastPaceStatus = 'over_pace' | 'on_pace' | 'under_pace';

export interface IForecastSeries {
    id: string;
    user_id: number;
    category_id: number;
    default_amount: number;
    default_notes?: string | null;
    is_active: boolean;
    latest_forecast?: IForecast | null;
}

export interface IForecast {
    id?: string;
    forecast_series_id?: string;
    category_id: number;
    category?: ICategory;
    amount: number;
    month: string;
    notes?: string | null;
    is_active: boolean;
    daily_allowance: number;
    spent_to_date: number;
    remaining: number;
    pace_status: ForecastPaceStatus;
    created_at?: string;
    updated_at?: string;
}

export interface IForecastInput {
    category_id: number;
    amount: number;
    month: string;
    notes?: string;
    apply_to_default?: boolean;
}
