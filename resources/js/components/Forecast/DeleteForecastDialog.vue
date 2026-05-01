<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { destroy } from '@/routes/forecasts';
import type { IForecast } from '@/types/models/forecast';
import { formatMonth } from '@/utils/formatDate';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  forecast: IForecast;
}>();

const { t } = useI18n();
const form = useForm({});

const deleteForecast = () => {
  if (!props.forecast.id) return;

  form.submit(destroy(props.forecast.id), {
    onSuccess: () => {
      open.value = false;
    },
  });
};

const description = computed(() => {
  if (!props.forecast.is_active) {
    return t('generic.confirm.deleteForecastSeries', {
      category: props.forecast.category?.name ?? '',
    });
  }

  return t('generic.confirm.deleteForecast', {
    category: props.forecast.category?.name ?? '',
    month: formatMonth(props.forecast.month),
  });
});
</script>

<template>
  <AlertDialog v-model:open="open">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
        <AlertDialogDescription>{{ description }}</AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>{{ t('generic.actions.cancel') }}</AlertDialogCancel>
        <AlertDialogAction variant="destructive" @click="deleteForecast" :disabled="form.processing">
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
