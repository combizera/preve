import type en from './locales/en.json';

type MessageSchema = typeof en;

declare module 'vue-i18n' {
    // eslint-disable-next-line @typescript-eslint/no-empty-object-type
    interface DefineLocaleMessage extends MessageSchema {}
}
