import { defineStore } from 'pinia';
import { ref } from 'vue';

import { TransactionType } from '@/types/models/transaction';

export const useRecurringStore = defineStore('recurring', () => {
    const showFormDialog = ref(false);
    const recurringType = ref<TransactionType>('expense');

    function openCreateDialog(type: TransactionType = 'expense') {
        recurringType.value = type;
        showFormDialog.value = true;
    }

    function closeCreateDialog() {
        showFormDialog.value = false;
    }

    return {
        showFormDialog,
        recurringType,
        openCreateDialog,
        closeCreateDialog,
    };
});
