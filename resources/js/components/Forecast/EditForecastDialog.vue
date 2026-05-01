<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
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
import { update } from '@/routes/forecasts';
import type { ICategory } from '@/types/models/category';
import type { IForecast, IForecastInput } from '@/types/models/forecast';
import { formatMonth } from '@/utils/formatDate';
import { validateAmount } from '@/utils/validateAmount';

const open = defineModel<boolean>('open', { required: true });

interface Props {
  forecast: IForecast;
  categories: ICategory[];
}

const props = defineProps<Props>();

const { t } = useI18n();

const form = useForm<IForecastInput>({
  category_id: props.forecast.category_id,
  amount: props.forecast.amount,
  month: props.forecast.month.slice(0, 7),
  notes: props.forecast.notes ?? '',
  apply_to_default: false,
});

const updateForecast = () => {
  const id = props.forecast.id;

  if (!id) return;
  if (!validateAmount(form, t)) return;

  form.submit(update(id), {
    onSuccess: () => {
      open.value = false;
    },
  });
};

const dialogDescription = computed(() =>
  t('forecasts.edit.description', {
    category: props.forecast.category?.name ?? '',
    month: formatMonth(props.forecast.month),
  }),
);

const editCategories = computed<ICategory[]>(() => {
  if (!props.forecast.category) return props.categories;

  return [props.forecast.category, ...props.categories];
});
</script>

<template>
  <Dialog v-model:open="open">
    <DialogContent class="sm:max-w-137.5">
      <DialogHeader>
        <DialogTitle>{{ t('forecasts.edit.title') }}</DialogTitle>
        <DialogDescription>{{ dialogDescription }}</DialogDescription>
      </DialogHeader>

      <FormForecast :form="form" :categories="editCategories" edit-mode />

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
        </DialogClose>
        <Button type="button" @click="updateForecast" :disabled="form.processing">
          {{ t('generic.actions.saveChanges') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
