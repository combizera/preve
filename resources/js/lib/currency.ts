import { usePage } from '@inertiajs/vue3';

import { CURRENCIES, type SupportedCurrency } from '@/config/currency';

/**
 * Returns the current user's currency code.
 */
export function getUserCurrency(): SupportedCurrency {
  const page = usePage();
  const user = (page.props.auth as { user: { currency?: string } })?.user;
  return (user?.currency as SupportedCurrency) ?? 'BRL';
}

/**
 * Returns the currency symbol for the current user's currency.
 * @returns Currency symbol (e.g., "R$", "$")
 */
export function getCurrencySymbol(): string {
  return CURRENCIES[getUserCurrency()].symbol;
}

/**
 * Formats a value in cents to display format based on user's currency.
 * @param cents - Value in cents (e.g., 12510 = 125.10)
 * @returns Formatted string (e.g., "125,10" for BRL, "125.10" for USD)
 */
export function formatCentsToDisplay(cents: number | string): string {
  const numericCents = typeof cents === 'string' ? parseInt(cents, 10) : cents;

  if (isNaN(numericCents)) {
    return '';
  }

  const config = CURRENCIES[getUserCurrency()];

  if (numericCents === 0) {
    return `0${config.decimalSeparator}00`;
  }

  const amount = numericCents / 100;
  return amount.toFixed(2).replace('.', config.decimalSeparator);
}

/**
 * Formats a value in cents into a compact currency label suited for chart axes
 * (no decimals, k-suffix for thousands).
 * @param cents - Value in cents (e.g., 250000 = 2500.00)
 * @returns Compact label (e.g., "R$ 3k", "R$ 250", "-R$ 1k", "R$ 0")
 */
export function formatCompactCurrency(cents: number): string {
  const symbol = getCurrencySymbol();
  if (cents === 0) return `${symbol} 0`;

  const abs = Math.abs(cents) / 100;
  const sign = cents < 0 ? '-' : '';
  const value = abs >= 1000 ? `${(abs / 1000).toFixed(0)}k` : abs.toFixed(0);

  return `${sign}${symbol} ${value}`;
}

/**
 * Converts a typed string to cents
 * @param input - String with numbers only (e.g., "12510")
 * @returns Value in cents (e.g., 12510)
 */
export function parseToCents(input: string): number {
  const numbers = input.replace(/\D/g, '');
  return numbers === '' ? 0 : parseInt(numbers, 10);
}

/**
 * Removes all non-numeric characters from a string
 * @param value - Input string
 * @returns String with numbers only
 */
export function extractNumbers(value: string): string {
  return value.replace(/\D/g, '');
}
