export function formatTransactionDate(dateString?: string | null): string {
    if (!dateString) {
        return '';
    }

    return new Date(dateString).toISOString().slice(0, 10);
}

/**
 * Formats a date string ("YYYY-MM" or "YYYY-MM-DD") as "MM/YYYY".
 */
export function formatMonth(value: string): string {
    const [year, month] = value.split('-');
    return `${month}/${year}`;
}
