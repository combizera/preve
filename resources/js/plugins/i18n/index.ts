import { createI18n } from 'vue-i18n';

import en from './locales/en.json';
import ptBR from './locales/pt-BR.json';

const i18n = createI18n({
    legacy: false,
    locale: 'pt-BR',
    fallbackLocale: 'en',
    messages: {
        en,
        'pt-BR': ptBR,
    },
});

export default i18n;
