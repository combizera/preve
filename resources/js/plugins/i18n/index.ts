import { createI18n } from 'vue-i18n';

import { BACKEND_TO_FRONTEND_LOCALE } from './locale-map';
import en from './locales/en.json';
import ptBR from './locales/pt-BR.json';

export type SupportedLocale = 'en' | 'pt-BR';

export const SUPPORTED_LOCALES: { value: SupportedLocale; label: string }[] = [
    { value: 'pt-BR', label: 'Português (Brasil)' },
    { value: 'en', label: 'English' },
];

function getInitialLocale(): SupportedLocale {
    if (typeof window === 'undefined') {
        return 'en';
    }

    const inertiaEl = document.getElementById('app');
    if (inertiaEl?.dataset.page) {
        const pageData = JSON.parse(inertiaEl.dataset.page);
        const backendLocale = pageData?.props?.auth?.user?.locale;
        if (backendLocale && BACKEND_TO_FRONTEND_LOCALE[backendLocale]) {
            return BACKEND_TO_FRONTEND_LOCALE[backendLocale];
        }
    }

    const browserLang = navigator.language;

    if (browserLang) {
        const baseBrowserLang = browserLang.split('-')[0].toLowerCase();

        const matchedLocale = SUPPORTED_LOCALES.find(
            (locale) =>
                locale.value.split('-')[0].toLowerCase() === baseBrowserLang,
        );

        if (matchedLocale) {
            return matchedLocale.value;
        }
    }

    return 'en';
}

const i18n = createI18n({
    legacy: false,
    locale: getInitialLocale(),
    fallbackLocale: 'en',
    messages: {
        en,
        'pt-BR': ptBR,
    },
});

export default i18n;
