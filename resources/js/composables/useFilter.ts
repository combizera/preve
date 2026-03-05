import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

export function useFilter<T extends Record<string, any>>(
    url: string,
    initialFilters: T,
    defaultFilters: T
) {
    const form = useForm(initialFilters);
    const activeCount = ref(0);

    watch(
        () => form.data(),
        (newData) => {
            let count = 0;
            for (const key in newData) {
                const value = newData[key];
                if (key === 'search') continue;

                if (Array.isArray(value) && value.length > 0) {
                    count++;
                } else if (value && value !== '' && value !== null && value !== 'all') {
                    count++;
                }
            }
            activeCount.value = count;
        },
        { immediate: true, deep: true }
    );

    const apply = () => {
        form.transform((data) => {
            const cleaned = { ...data };
            for (const key in cleaned) {
                if (cleaned[key] === 'all' || cleaned[key] === null) {
                    cleaned[key] = '';
                }
            }
            return cleaned;
        }).get(url, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const clear = () => {
        form.defaults(defaultFilters);
        form.reset();
        apply();
    };

    return {
        form,
        activeCount,
        apply,
        clear,
    };
}
