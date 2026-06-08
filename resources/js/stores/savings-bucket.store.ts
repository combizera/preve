import { defineStore } from 'pinia';
import { ref } from 'vue';

import type { ISavingsBucket } from '@/types/models/savings-bucket';

export const useSavingsBucketStore = defineStore('savings-bucket', () => {
    const bucket = ref<ISavingsBucket | null>(null);
    const showEditDialog = ref(false);
    const showDeleteDialog = ref(false);

    function openEditModal(item: ISavingsBucket) {
        bucket.value = item;
        showEditDialog.value = true;
    }

    function closeEditModal() {
        showEditDialog.value = false;
        bucket.value = null;
    }

    function openDeleteModal(item: ISavingsBucket) {
        bucket.value = item;
        showDeleteDialog.value = true;
    }

    function closeDeleteModal() {
        showDeleteDialog.value = false;
        bucket.value = null;
    }

    return {
        bucket,
        showEditDialog,
        showDeleteDialog,
        openEditModal,
        closeEditModal,
        openDeleteModal,
        closeDeleteModal,
    };
});
