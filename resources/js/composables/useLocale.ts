import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import { type SupportedLocale, SUPPORTED_LOCALES } from '@/plugins/i18n';

export function useLocale() {
    const { locale } = useI18n();

    onMounted(() => {
        const saved = localStorage.getItem('locale') as SupportedLocale | null;

        if (saved && SUPPORTED_LOCALES.some((l) => l.value === saved)) {
            locale.value = saved;
        }
    });

    function updateLocale(value: SupportedLocale) {
        localStorage.setItem('locale', value);
        window.location.reload();
    }

    return {
        locale,
        supportedLocales: SUPPORTED_LOCALES,
        updateLocale,
    };
}
