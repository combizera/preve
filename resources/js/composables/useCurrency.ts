import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

import { type SupportedCurrency, SUPPORTED_CURRENCIES } from '@/config/currency';
import { currency as currencyRoute } from '@/routes/profile';

export function useCurrency() {
    const page = usePage();

    const currency = computed<SupportedCurrency>(() => {
        const user = (page.props.auth as { user: { currency?: string } })?.user;
        return (user?.currency as SupportedCurrency) ?? 'BRL';
    });

    function updateCurrency(value: SupportedCurrency) {
        router.patch(
            currencyRoute().url,
            { currency: value },
            { preserveState: true },
        );
    }

    return {
        currency,
        supportedCurrencies: SUPPORTED_CURRENCIES,
        updateCurrency,
    };
}
