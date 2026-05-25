import { onMounted, ref, watch } from 'vue';

export type TransactionView = 'list' | 'table';

const STORAGE_KEY = 'transactions.view';
const DEFAULT_VIEW: TransactionView = 'list';

const view = ref<TransactionView>(DEFAULT_VIEW);

function isValidView(value: unknown): value is TransactionView {
    return value === 'list' || value === 'table';
}

export function useTransactionView() {
    onMounted(() => {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (isValidView(stored)) {
            view.value = stored;
        }
    });

    watch(view, (next) => {
        localStorage.setItem(STORAGE_KEY, next);
    });

    return { view };
}
