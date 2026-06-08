import { defineStore } from 'pinia';
import { ref } from 'vue';

import type { ICreditCard } from '@/types/models/credit-card';

export const useCreditCardStore = defineStore('credit-card', () => {
    const creditCard = ref<ICreditCard | null>(null);
    const showEditDialog = ref(false);
    const showDeleteDialog = ref(false);

    function openEditModal(item: ICreditCard) {
        creditCard.value = item;
        showEditDialog.value = true;
    }

    function closeEditModal() {
        showEditDialog.value = false;
        creditCard.value = null;
    }

    function openDeleteModal(item: ICreditCard) {
        creditCard.value = item;
        showDeleteDialog.value = true;
    }

    function closeDeleteModal() {
        showDeleteDialog.value = false;
        creditCard.value = null;
    }

    return {
        creditCard,
        showEditDialog,
        showDeleteDialog,
        openEditModal,
        closeEditModal,
        openDeleteModal,
        closeDeleteModal,
    };
});
