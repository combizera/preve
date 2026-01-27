/**
 * Formats a value in cents to Brazilian currency display format
 * @param cents - Value in cents (e.g., 12510 = R$ 125.10)
 * @returns Formatted string (e.g., "125,10")
 */
export function formatCentsToDisplay(cents: number | string): string {
  const numericCents = typeof cents === 'string' ? parseInt(cents, 10) : cents;

  if (isNaN(numericCents)) {
    return '';
  }

  if (numericCents === 0) {
    return '0,00';
  }

  const amount = numericCents / 100;
  return amount.toFixed(2).replace('.', ',');
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
