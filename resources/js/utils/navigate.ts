import { router } from '@inertiajs/vue3';

type Params = Record<string, any>;

export function paginationNavigate(params: Params) {
    router.get(window.location.pathname, getQueryParams(params), {
        preserveState: true,
        preserveScroll: true,
    });
}

export function getQueryParams(overrides: Params) {
    const params = new URLSearchParams(window.location.search);

    Object.entries(overrides).forEach(([key, value]) => {
        if (value === null || value === undefined) {
            params.delete(key);
        } else {
            params.set(key, String(value));
        }
    });

    return Object.fromEntries(params.entries());
}
