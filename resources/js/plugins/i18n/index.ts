import { createI18n } from 'vue-i18n';

import en from './locales/en.json';
import ptBR from './locales/pt-BR.json';

export type SupportedLocale = 'en' | 'pt-BR';

export const SUPPORTED_LOCALES: { value: SupportedLocale; label: string }[] = [
    { value: 'pt-BR', label: 'Português (Brasil)' },
    { value: 'en', label: 'English' },
];

function getStoredLocale(): SupportedLocale {
    if (typeof window === 'undefined') {
        return 'pt-BR';
    }

    const stored = localStorage.getItem('locale') as SupportedLocale | null;

    if (stored && SUPPORTED_LOCALES.some((l) => l.value === stored)) {
        return stored;
    }

    return 'pt-BR';
}

const i18n = createI18n({
    legacy: false,
    locale: getStoredLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        'pt-BR': ptBR,
    },
});

export default i18n;
