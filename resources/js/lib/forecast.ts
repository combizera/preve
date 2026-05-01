import transactionRoutes from '@/routes/transactions';
import type { ForecastPaceStatus, IForecast } from '@/types/models/forecast';

export interface PaceClasses {
    badge: string;
    bar: string;
}

const PACE_CLASS_MAP: Record<ForecastPaceStatus, PaceClasses> = {
    over_pace: {
        badge: 'bg-destructive/10 text-destructive border-destructive/30',
        bar: 'bg-destructive',
    },
    under_pace: {
        badge: 'bg-positive/10 text-positive border-positive/30',
        bar: 'bg-positive',
    },
    on_pace: {
        badge: 'bg-muted text-muted-foreground',
        bar: 'bg-foreground/70',
    },
};

export function getPaceClasses(status: ForecastPaceStatus): PaceClasses {
    return PACE_CLASS_MAP[status] ?? PACE_CLASS_MAP.on_pace;
}

export function getProgressPercent(spent: number, total: number): number {
    if (total <= 0) return 0;
    return Math.min(100, Math.round((spent / total) * 100));
}

export function buildForecastTransactionsUrl(forecast: IForecast): string {
    const [year, month] = forecast.month.split('-');
    const lastDay = new Date(Number(year), Number(month), 0).getDate();

    const params = new URLSearchParams();
    params.append('categories[]', String(forecast.category_id));
    params.append('date_start', `${year}-${month}-01`);
    params.append('date_end', `${year}-${month}-${String(lastDay).padStart(2, '0')}`);

    return `${transactionRoutes.index().url}?${params.toString()}`;
}
