import { capitalizeFirstLetter } from './utils';

import type { FrequencyType } from '@/types/models/recurring-transaction';

/**
 * Formats the frequency text with the day of month
 * @param frequency - The frequency type (monthly or yearly)
 * @param dayOfMonth - The day of the month (1-31)
 * @returns Formatted frequency text (e.g., "Monthly on day 15")
 */
export function formatFrequency(
  frequency: FrequencyType,
  dayOfMonth: number,
): string {
  const freq = capitalizeFirstLetter(frequency);
  return `${freq} on day ${dayOfMonth}`;
}

/**
 * Calculates the next occurrence date for a recurring transaction
 * @param frequency - The frequency type (monthly or yearly)
 * @param dayOfMonth - The day of the month when it recurs
 * @returns Formatted next occurrence date in en format
 */
export function calculateNextOccurrence(
  frequency: FrequencyType,
  dayOfMonth: number,
): string {
  const today = new Date();
  let nextDate = new Date(today.getFullYear(), today.getMonth(), dayOfMonth);

  if (nextDate < today) {
    if (frequency === 'monthly') {
      nextDate = new Date(today.getFullYear(), today.getMonth() + 1, dayOfMonth);
    } else {
      nextDate = new Date(today.getFullYear() + 1, today.getMonth(), dayOfMonth);
    }
  }

  return nextDate.toLocaleDateString('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
}

/**
 * Formats the active period of a recurring transaction
 * @param startDate - The start date string
 * @param endDate - The optional end date string
 * @returns Formatted period text (e.g., "15 de jan de 2026 - 15 de dez de 2026" or "Since 15 de jan de 2026")
 */
export function formatActivePeriod(
  startDate: string,
  endDate?: string | null,
): string {
  const start = new Date(startDate);
  const formattedStart = start.toLocaleDateString('en', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });

  if (endDate) {
    const end = new Date(endDate);
    const formattedEnd = end.toLocaleDateString('en', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
    return `${formattedStart} - ${formattedEnd}`;
  }

  return `Since ${formattedStart}`;
}

/**
 * Calculates the annual cost/income of a recurring transaction
 * @param frequency - The frequency type (monthly or yearly)
 * @param amount - The transaction amount in cents
 * @returns Annual amount in cents
 */
export function calculateAnnualAmount(
  frequency: FrequencyType,
  amount: number,
): number {
  return frequency === 'monthly' ? amount * 12 : amount;
}

/**
 * Calculates the monthly cost/income of a recurring transaction
 * @param frequency - The frequency type (monthly or yearly)
 * @param amount - The transaction amount in cents
 * @returns Monthly amount in cents
 */
export function calculateMonthlyAmount(
  frequency: FrequencyType,
  amount: number,
): number {
  return frequency === 'monthly' ? amount : Math.round(amount / 12);
}

/**
 * Calculates the total monthly amount from an array of recurring transactions
 * Only includes active transactions in the calculation
 * @param transactions - Array of recurring transactions
 * @returns Total monthly amount in cents
 * @example
 * const total = calculateTotalMonthly(incomeTransactions);
 * // Returns sum of all monthly amounts in cents for active transactions only
 */
export function calculateTotalMonthly(
  transactions: Array<{
    frequency: FrequencyType;
    amount: number;
    is_active: boolean;
  }>,
): number {
  return transactions
    .filter((transaction) => transaction.is_active)
    .reduce((accumulator: number, transaction) => {
      const monthlyAmount = calculateMonthlyAmount(
        transaction.frequency,
        transaction.amount,
      );
      return accumulator + monthlyAmount;
    }, 0);
}
