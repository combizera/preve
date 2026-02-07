export function formatTransactionDate(dateString?: string): string {
    let date = new Date()

    if (dateString) {
        date = new Date(dateString);
    }

    return date.toISOString().slice(0, 10);
}
