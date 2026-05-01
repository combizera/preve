import type { InertiaForm } from '@inertiajs/vue3';
import type { ComposerTranslation } from 'vue-i18n';

/**
 * Validates that the form amount is greater than zero.
 * Sets an error on the form if invalid.
 *
 * @param form - Inertia form with an `amount` field
 * @param t - i18n translation function
 * @returns true if valid, false otherwise
 */
export function validateAmount(
  form: InertiaForm<{ amount: number }>,
  t: ComposerTranslation,
): boolean {
  if (form.amount <= 0) {
    form.setError('amount', t('validation.amountRequired'));
    return false;
  }

  return true;
}
