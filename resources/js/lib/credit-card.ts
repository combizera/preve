/**
 * The day cash actually leaves the account for a purchase made on the given
 * date: the due day of the invoice the purchase closes into. Mirrors the backend
 * CreditCard::effectivePaymentDate so the UI can preview card billing dates.
 *
 * @param closingDay - The card's statement closing day (1-31)
 * @param dueDay - The card's payment due day (1-31)
 * @param purchaseDate - When the purchase happens
 * @returns The date the invoice is due
 */
export function effectivePaymentDate(
    closingDay: number,
    dueDay: number,
    purchaseDate: Date,
): Date {
    const monthsUntilClose = purchaseDate.getDate() <= closingDay ? 0 : 1;
    const monthsCloseToDue = dueDay > closingDay ? 0 : 1;

    const year = purchaseDate.getFullYear();
    const month = purchaseDate.getMonth() + monthsUntilClose + monthsCloseToDue;
    const lastDayOfMonth = new Date(year, month + 1, 0).getDate();

    return new Date(year, month, Math.min(dueDay, lastDayOfMonth));
}
