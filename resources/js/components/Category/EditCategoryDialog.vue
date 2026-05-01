<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import CategoryAppearanceSection from '@/components/Category/sections/CategoryAppearanceSection.vue';
import CategoryBudgetSection from '@/components/Category/sections/CategoryBudgetSection.vue';
import CategoryDetailsSection from '@/components/Category/sections/CategoryDetailsSection.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { update as updateCategoryRoute } from '@/routes/categories';
import { store as storeForecast, update as updateForecast } from '@/routes/forecasts';
import type { ICategory, ICategoryForm } from '@/types/models/category';
import type { IForecastInput } from '@/types/models/forecast';
import { validateAmount } from '@/utils/validateAmount';

export type CategorySection = 'details' | 'appearance' | 'budget';

const { t } = useI18n();
const open = defineModel<boolean>('open', { required: true });

interface Props {
  category: ICategory;
  defaultSection?: CategorySection;
}

const props = withDefaults(defineProps<Props>(), {
  defaultSection: 'details',
});

const isExpense = computed(() => props.category.type === 'expense');

const appearanceOpen = ref(props.defaultSection === 'appearance');
const budgetOpen = ref(props.defaultSection === 'budget' && isExpense.value);

const series = computed(() => props.category.forecast_series ?? null);
const latestForecast = computed(() => series.value?.latest_forecast ?? null);

const categoryForm = useForm<ICategoryForm>({
  name: props.category.name,
  type: props.category.type,
  description: props.category.description ?? '',
  icon: props.category.icon,
  color: props.category.color,
});

const today = new Date();
const currentMonth = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;

const budgetForm = useForm<IForecastInput>({
  category_id: props.category.id,
  amount: series.value?.default_amount ?? 0,
  month: currentMonth,
  notes: series.value?.default_notes ?? '',
  apply_to_default: false,
});

const dialogDescription = computed(() =>
  t('categories.edit.description', { name: props.category.name }),
);

const save = () => {
  const persistBudget = () => {
    if (!isExpense.value || !budgetForm.isDirty) {
      open.value = false;
      return;
    }

    if (budgetForm.amount <= 0 && !validateAmount(budgetForm, t)) {
      return;
    }

    if (series.value && latestForecast.value?.id) {
      budgetForm.submit(updateForecast(latestForecast.value.id), {
        preserveScroll: true,
        onSuccess: () => {
          open.value = false;
        },
      });
      return;
    }

    budgetForm.submit(storeForecast(), {
      preserveScroll: true,
      onSuccess: () => {
        open.value = false;
      },
    });
  };

  if (categoryForm.isDirty) {
    categoryForm.submit(updateCategoryRoute(props.category.id), {
      preserveScroll: true,
      onSuccess: persistBudget,
    });
    return;
  }

  persistBudget();
};

const isProcessing = computed(
  () => categoryForm.processing || budgetForm.processing,
);
</script>

<template>
  <Dialog v-model:open="open">
    <DialogContent class="sm:max-w-150">
      <DialogHeader>
        <DialogTitle>{{ t('categories.edit.title') }}</DialogTitle>
        <DialogDescription>{{ dialogDescription }}</DialogDescription>
      </DialogHeader>

      <div class="grid gap-3">
        <CategoryDetailsSection :form="categoryForm">
          <CategoryAppearanceSection v-model:open="appearanceOpen" :form="categoryForm" />
          <CategoryBudgetSection
            v-if="isExpense"
            v-model:open="budgetOpen"
            :category="category"
            :form="budgetForm"
          />
        </CategoryDetailsSection>
      </div>

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
        </DialogClose>
        <Button type="button" @click="save" :disabled="isProcessing">
          {{ t('generic.actions.saveChanges') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
