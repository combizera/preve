import { createI18n } from 'vue-i18n';

import en from './locales/en.json';
import ptBR from './locales/pt-BR.json';

// TODO: validar se estamos carregando os 2 idiomas, ou se estamos carregando o idioma correto de acordo com a configuração do sistema operacional do usuário
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
