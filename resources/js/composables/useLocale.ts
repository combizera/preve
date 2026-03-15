import { useI18n } from 'vue-i18n';
import { usePage, router } from '@inertiajs/vue3';

import { type SupportedLocale, SUPPORTED_LOCALES } from '@/plugins/i18n';
import { BACKEND_TO_FRONTEND_LOCALE, FRONTEND_TO_BACKEND_LOCALE } from '@/plugins/i18n/locale-map';
import { locale as localeRoute } from '@/routes/profile';

export function useLocale() {
    const { locale } = useI18n();
    const page = usePage();

    const user = (page.props.auth as { user: { locale?: string } })?.user;
    const backendLocale = user?.locale;
    locale.value = backendLocale ? (BACKEND_TO_FRONTEND_LOCALE[backendLocale] ?? 'en') : 'en';

    function updateLocale(value: SupportedLocale) {
        locale.value = value;

        router.patch(
            localeRoute().url,
            { locale: FRONTEND_TO_BACKEND_LOCALE[value] },
            { preserveState: true },
        );
    }

    return {
        locale,
        supportedLocales: SUPPORTED_LOCALES,
        updateLocale,
    };
}
