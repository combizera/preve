import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useTransactionStore = defineStore('transaction', () => {
    const showFormDialog = ref(false);

    function openCreateDialog() {
        showFormDialog.value = true;
    }

    function closeCreateDialog() {
        showFormDialog.value = false;
    }

    return {
        showFormDialog,
        openCreateDialog,
        closeCreateDialog,
    };
});
