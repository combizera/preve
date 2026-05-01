<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ChevronDown, Pause, Play, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import BudgetFormFields from '@/components/Forecast/BudgetFormFields.vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { destroy as destroySeries } from '@/routes/forecast-series';
import { toggle as toggleForecast } from '@/routes/forecasts';
import type { ICategory } from '@/types/models/category';
import type { IForecastInput } from '@/types/models/forecast';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  category: ICategory;
  form: InertiaForm<IForecastInput>;
}>();

const { t } = useI18n();

const series = computed(() => props.category.forecast_series ?? null);
const latestForecast = computed(() => series.value?.latest_forecast ?? null);
const hasSeries = computed(() => series.value !== null);
const isPaused = computed(() => !!series.value && !series.value.is_active);

const headerSummary = computed(() => {
  if (!series.value) return t('categories.budget.notSet');

  return `${getCurrencySymbol()} ${formatCentsToDisplay(series.value.default_amount)}/${t('categories.table.perMonthShort')}`;
});

const togglePause = () => {
  if (!latestForecast.value?.id) return;
  router.patch(toggleForecast(latestForecast.value.id).url, {}, { preserveScroll: true });
};

const showRemoveDialog = ref(false);

const removeBudget = () => {
  if (!series.value) return;
  router.delete(destroySeries(series.value.id).url, { preserveScroll: true });
  showRemoveDialog.value = false;
};
</script>

<template>
  <Collapsible v-model:open="open" class="rounded-md border">
    <CollapsibleTrigger
      class="flex w-full items-center justify-between gap-2 px-4 py-3 text-sm font-medium hover:bg-muted/50 cursor-pointer"
    >
      <div class="flex min-w-0 items-center gap-2">
        <span>{{ t('categories.sections.budget') }}</span>
        <span class="text-muted-foreground" aria-hidden="true">•</span>
        <span class="truncate text-xs font-normal text-muted-foreground">
          {{ headerSummary }}
        </span>
        <Badge
          v-if="isPaused"
          variant="secondary"
          class="px-1.5 py-0 text-xs"
        >
          {{ t('generic.labels.paused') }}
        </Badge>
      </div>
      <ChevronDown
        :size="16"
        class="shrink-0 text-muted-foreground transition-transform"
        :class="{ 'rotate-180': open }"
      />
    </CollapsibleTrigger>

    <CollapsibleContent class="border-t px-4 py-4">
      <div v-if="hasSeries" class="mb-3 flex items-center justify-end gap-2">
        <Button
          type="button"
          variant="ghost"
          size="sm"
          class="gap-1 text-xs"
          @click="togglePause"
        >
          <Pause v-if="!isPaused" :size="14" />
          <Play v-else :size="14" />
          {{ isPaused ? t('generic.actions.resume') : t('generic.actions.pause') }}
        </Button>

        <AlertDialog v-model:open="showRemoveDialog">
          <AlertDialogTrigger as-child>
            <Button type="button" variant="ghost" size="sm" class="gap-1 text-xs text-destructive hover:text-destructive">
              <Trash2 :size="14" />
              {{ t('categories.budget.remove') }}
            </Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
              <AlertDialogDescription>
                {{ t('categories.budget.removeConfirm', { name: category.name }) }}
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>{{ t('generic.actions.cancel') }}</AlertDialogCancel>
              <AlertDialogAction variant="destructive" @click="removeBudget">
                {{ t('generic.actions.confirm') }}
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>

      <BudgetFormFields :form="form" :show-apply-to-default="hasSeries" />
    </CollapsibleContent>
  </Collapsible>
</template>
