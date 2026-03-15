import type { SupportedLocale } from './index';

/**
 * Maps backend locale codes (underscore) to frontend locale codes (hyphen).
 * e.g. 'pt_BR' -> 'pt-BR'
 *
 * To add a new language:
 * 1. Add the backend -> frontend mapping here
 * 2. Add the frontend -> backend mapping in FRONTEND_TO_BACKEND_LOCALE
 * 3. Add the locale to SUPPORTED_LOCALES in index.ts
 * 4. Create the translation file in locales/
 * 5. Create the backend translation files in lang/
 */
export const BACKEND_TO_FRONTEND_LOCALE: Record<string, SupportedLocale> = {
    pt_BR: 'pt-BR',
    en: 'en',
};

export const FRONTEND_TO_BACKEND_LOCALE: Record<SupportedLocale, string> = {
    'pt-BR': 'pt_BR',
    en: 'en',
};
