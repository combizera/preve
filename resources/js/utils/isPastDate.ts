export function isPastDate(dateToCompare: Date | string | number): boolean {
    const date = new Date(dateToCompare);

    if (isNaN(date.getTime())) {
        throw new Error('Data inválida fornecida.');
    }

    const now = new Date();
    now.setHours(0, 0, 0, 0);

    return date.getTime() < now.getTime();
}
