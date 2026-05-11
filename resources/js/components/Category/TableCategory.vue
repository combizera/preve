<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { GripVertical } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteCategoryDialog from '@/components/Category/DeleteCategoryDialog.vue';
import EditCategoryDialog, {
  type CategorySection,
} from '@/components/Category/EditCategoryDialog.vue';
import SectionTitle from '@/components/SectionTitle.vue';
import { Badge } from '@/components/ui/badge';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import SetBudgetButton from '@/components/ui/button/SetBudgetButton.vue';
import {
  Table,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { getColorClass } from '@/lib/accent-colors';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { reorder as reorderRoute } from '@/routes/categories';
import { ICategory } from '@/types/models/category';

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedCategory = ref<ICategory | null>(null);
const editingDefaultSection = ref<CategorySection>('details');

const openEditDialog = (category: ICategory) => {
  selectedCategory.value = category;
  editingDefaultSection.value = 'details';
  showEditDialog.value = true;
};

const openDeleteDialog = (category: ICategory) => {
  selectedCategory.value = category;
  showDeleteDialog.value = true;
};

const onBudgetAction = (category: ICategory) => {
  selectedCategory.value = category;
  editingDefaultSection.value = 'budget';
  showEditDialog.value = true;
};

const { t } = useI18n();

interface Props {
  categories: ICategory[];
  type?: 'income' | 'expense';
}

const props = withDefaults(defineProps<Props>(), {
  type: 'expense',
});

const typeLabel = computed(() =>
  props.type === 'income'
    ? t('models.transaction.income')
    : t('models.transaction.expense'),
);

const isExpense = computed(() => props.type === 'expense');

const localCategories = ref<ICategory[]>([...props.categories]);

watch(
  () => props.categories,
  (next) => {
    localCategories.value = [...next];
  },
  { deep: false },
);

const persistOrder = () => {
  router.patch(
    reorderRoute.url(),
    {
      type: props.type,
      ids: localCategories.value.map((category) => category.id),
    },
    {
      preserveScroll: true,
      preserveState: true,
      only: ['incomeCategories', 'expenseCategories'],
    },
  );
};
</script>

<template>
  <div>
    <div class="my-1 px-2 pb-3">
      <SectionTitle :title="typeLabel" />
    </div>
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-8"></TableHead>
          <TableHead>{{ t('generic.labels.name') }}</TableHead>
          <TableHead>{{ t('models.transaction.description') }}</TableHead>
          <TableHead v-if="isExpense">{{
            t('categories.table.budget')
          }}</TableHead>
          <TableHead class="text-right">{{
            t('generic.labels.actions')
          }}</TableHead>
        </TableRow>
      </TableHeader>
      <draggable
        tag="tbody"
        :list="localCategories"
        item-key="id"
        handle=".category-drag-handle"
        ghost-class="opacity-40"
        animation="150"
        class="[&_tr:last-child]:border-0"
        @end="persistOrder"
      >
        <template #item="{ element: category }">
          <TableRow class="group">
            <TableCell class="w-8">
              <button
                type="button"
                class="category-drag-handle flex cursor-grab items-center justify-center rounded p-1 text-muted-foreground hover:text-foreground active:cursor-grabbing"
                :aria-label="t('generic.labels.reorder')"
              >
                <GripVertical :size="16" />
              </button>
            </TableCell>
            <TableCell class="flex items-center gap-3">
              <div
                :class="[
                  getColorClass(category.color, 'bg'),
                  getColorClass(category.color, 'border'),
                  'inline-flex items-center justify-center rounded border p-1.5',
                ]"
              >
                <component
                  :is="getIconComponent(category.icon)"
                  :size="18"
                  :class="getColorClass(category.color, 'text')"
                />
              </div>
              <p class="font-medium">
                {{ category.name }}
              </p>
            </TableCell>
            <TableCell>
              <p class="text-sm text-muted-foreground">
                {{
                  category.description && category.description.length > 40
                    ? category.description.slice(0, 40) + '...'
                    : category.description
                }}
              </p>
            </TableCell>
            <TableCell v-if="isExpense" class="whitespace-nowrap">
              <div
                v-if="category.forecast_series"
                class="flex items-center gap-2"
              >
                <span class="text-sm font-medium text-foreground">
                  {{ getCurrencySymbol() }}
                  {{
                    formatCentsToDisplay(
                      category.forecast_series.default_amount,
                    )
                  }}<span class="text-muted-foreground"
                    >/{{ t('categories.table.perMonthShort') }}</span
                  >
                </span>
                <Badge
                  v-if="!category.forecast_series.is_active"
                  variant="secondary"
                  class="px-1.5 py-0 text-xs"
                >
                  {{ t('generic.labels.paused') }}
                </Badge>
              </div>
              <span v-else class="text-sm text-muted-foreground">—</span>
            </TableCell>
            <TableCell class="text-right">
              <ActionGroup>
                <EditButton @click="openEditDialog(category)" />

                <SetBudgetButton
                  v-if="isExpense"
                  :has-series="!!category.forecast_series"
                  @click="onBudgetAction(category)"
                />

                <DeleteButton @click="openDeleteDialog(category)" />
              </ActionGroup>
            </TableCell>
          </TableRow>
        </template>
      </draggable>

      <EditCategoryDialog
        v-if="showEditDialog && selectedCategory"
        v-model:open="showEditDialog"
        :category="selectedCategory"
        :default-section="editingDefaultSection"
      />

      <DeleteCategoryDialog
        v-model:open="showDeleteDialog"
        :category="selectedCategory"
      />
    </Table>
  </div>
</template>
