import { computed, ref, watch, type Ref } from 'vue';

/**
 * Manages a set of selected row ids for a table, keeping the selection in sync
 * with the currently visible rows (ids that disappear are dropped).
 */
export function useRowSelection<T>(
    items: Ref<T[]>,
    getId: (item: T) => string | undefined,
) {
    const selectedIds = ref<string[]>([]);

    const selectableIds = computed(() =>
        items.value.map(getId).filter((id): id is string => Boolean(id)),
    );

    watch(items, () => {
        const visible = new Set(selectableIds.value);
        selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
    });

    const allSelected = computed(
        () =>
            selectableIds.value.length > 0 &&
            selectableIds.value.every((id) => selectedIds.value.includes(id)),
    );

    const headerState = computed<boolean | 'indeterminate'>(() => {
        if (allSelected.value) return true;
        return selectedIds.value.length > 0 ? 'indeterminate' : false;
    });

    const toggleAll = (checked: boolean | 'indeterminate') => {
        selectedIds.value = checked === true ? [...selectableIds.value] : [];
    };

    const toggleRow = (id: string, checked: boolean | 'indeterminate') => {
        if (checked === true) {
            if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
        } else {
            selectedIds.value = selectedIds.value.filter(
                (value) => value !== id,
            );
        }
    };

    const isSelected = (id: string) => selectedIds.value.includes(id);

    const clear = () => {
        selectedIds.value = [];
    };

    return {
        selectedIds,
        headerState,
        toggleAll,
        toggleRow,
        isSelected,
        clear,
    };
}
