import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useForecastStore = defineStore('forecast', () => {
    const showFormDialog = ref(false);
    const presetCategoryId = ref<number | null>(null);
    const editForecastSeriesId = ref<string | null>(null);

    function openCreateDialog(categoryId: number | null = null) {
        presetCategoryId.value = categoryId;
        showFormDialog.value = true;
    }

    function closeCreateDialog() {
        showFormDialog.value = false;
        presetCategoryId.value = null;
    }

    function requestEdit(seriesId: string) {
        editForecastSeriesId.value = seriesId;
    }

    function clearEditRequest() {
        editForecastSeriesId.value = null;
    }

    return {
        showFormDialog,
        presetCategoryId,
        editForecastSeriesId,
        openCreateDialog,
        closeCreateDialog,
        requestEdit,
        clearEditRequest,
    };
});
