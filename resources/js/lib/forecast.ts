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

export function getMonthProgressPercent(month: string, now: Date = new Date()): number {
    const [year, monthNumber] = month.split('-').map(Number);
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1;

    if (currentYear > year || (currentYear === year && currentMonth > monthNumber)) return 100;
    if (currentYear < year || (currentYear === year && currentMonth < monthNumber)) return 0;

    const daysInMonth = new Date(year, monthNumber, 0).getDate();
    return Math.round((now.getDate() / daysInMonth) * 100);
}

export function getCurrentMonthString(now: Date = new Date()): string {
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

export function isInMonth(forecastMonth: string, monthString: string): boolean {
    return forecastMonth.slice(0, 7) === monthString;
}

export interface ForecastTotals {
    amount: number;
    spent: number;
    remaining: number;
    dailyAllowance: number;
    activeCount: number;
    paceStatus: ForecastPaceStatus;
}

export function summarizeForecasts(
    forecasts: IForecast[],
    monthString: string,
    now: Date = new Date(),
): ForecastTotals {
    const monthForecasts = forecasts.filter(
        (forecast) => forecast.is_active && isInMonth(forecast.month, monthString),
    );

    const totals = monthForecasts.reduce(
        (acc, forecast) => ({
            amount: acc.amount + forecast.amount,
            spent: acc.spent + forecast.spent_to_date,
            remaining: acc.remaining + forecast.remaining,
            dailyAllowance: acc.dailyAllowance + forecast.daily_allowance,
        }),
        { amount: 0, spent: 0, remaining: 0, dailyAllowance: 0 },
    );

    const monthProgress = getMonthProgressPercent(monthString, now);
    const expected = (totals.amount * monthProgress) / 100;
    const paceStatus: ForecastPaceStatus =
        totals.amount === 0 || expected === 0
            ? 'on_pace'
            : totals.spent > expected * 1.05
              ? 'over_pace'
              : totals.spent < expected * 0.95
                ? 'under_pace'
                : 'on_pace';

    return {
        ...totals,
        activeCount: monthForecasts.length,
        paceStatus,
    };
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
