import { defineStore } from 'pinia';
import { ref } from 'vue';

import { ITag } from '@/types/models/tag';

export const useTagStore = defineStore('tag', () => {
    const tag = ref<ITag | null>(null);
    const showEditDialog = ref(false);
    const showDeleteDialog = ref(false);

    function openEditModal(item: ITag) {
        tag.value = item;
        showEditDialog.value = true;
    }

    function closeEditModal() {
        showEditDialog.value = false;
        tag.value = null;
    }

    function openDeleteModal(item: ITag) {
        tag.value = item;
        showDeleteDialog.value = true;
    }

    function closeDeleteModal() {
        showDeleteDialog.value = false;
        tag.value = null;
    }

    return {
        showEditDialog,
        tag,
        openEditModal,
        closeEditModal,
        showDeleteDialog,
        openDeleteModal,
        closeDeleteModal,
    };
});
