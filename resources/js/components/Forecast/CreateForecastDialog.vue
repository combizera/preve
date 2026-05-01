<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import FormForecast from '@/components/Forecast/FormForecast.vue';
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
import {
  extractNumbers,
  formatCentsToDisplay,
  parseToCents,
} from '@/lib/currency';
import { store } from '@/routes/forecasts';
import { useForecastStore } from '@/stores/forecast.store';
import type { ICategory } from '@/types/models/category';
import type { IForecastInput } from '@/types/models/forecast';
import { validateAmount } from '@/utils/validateAmount';

interface Props {
  categories: ICategory[];
}

defineProps<Props>();

const { t } = useI18n();
const forecastStore = useForecastStore();
const { showFormDialog } = storeToRefs(forecastStore);

const rawAmount = ref('');

const today = new Date();
const currentMonth = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;

const form = useForm<IForecastInput>({
  category_id: 0,
  amount: 0,
  month: currentMonth,
  notes: '',
});

const displayAmount = computed({
  get: () => formatCentsToDisplay(rawAmount.value),
  set: (value: string) => {
    const numbers = extractNumbers(value);
    rawAmount.value = numbers;
    form.amount = parseToCents(numbers);
  },
});

const createForecast = () => {
  if (!validateAmount(form, t)) return;

  form.submit(store(), {
    onSuccess: () => {
      forecastStore.closeCreateDialog();
      form.reset();
      rawAmount.value = '';
    },
  });
};
</script>

<template>
  <Dialog v-model:open="showFormDialog">
    <DialogContent class="sm:max-w-137.5">
      <DialogHeader>
        <DialogTitle>{{ t('forecasts.create.title') }}</DialogTitle>
        <DialogDescription>{{ t('forecasts.create.description') }}</DialogDescription>
      </DialogHeader>

      <FormForecast
        :form="form"
        v-model:displayAmount="displayAmount"
        :categories="categories"
      />

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
        </DialogClose>
        <Button type="button" @click="createForecast" :disabled="form.processing">
          {{ t('forecasts.create.button') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
