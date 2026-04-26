export function formatTransactionDate(dateString?: string | null): string {
    if (!dateString) {
        return '';
    }

    return new Date(dateString).toISOString().slice(0, 10);
}
