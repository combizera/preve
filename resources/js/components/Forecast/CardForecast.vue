<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteForecastDialog from '@/components/Forecast/DeleteForecastDialog.vue';
import EditForecastDialog from '@/components/Forecast/EditForecastDialog.vue';
import { Badge } from '@/components/ui/badge';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import ToggleActiveButton from '@/components/ui/button/ToggleActiveButton.vue';
import { Card } from '@/components/ui/card';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { cn } from '@/lib/utils';
import { toggle as toggleForecast } from '@/routes/forecasts';
import { useForecastStore } from '@/stores/forecast.store';
import type { ICategory } from '@/types/models/category';
import type { IForecast } from '@/types/models/forecast';
import { formatMonth } from '@/utils/formatDate';

const props = defineProps<{
  forecast: IForecast;
  categories: ICategory[];
}>();

const { t } = useI18n();

const showEditDialog = ref(false);
const showDeleteDialog = ref(false);

const forecastStore = useForecastStore();
const { editForecastSeriesId } = storeToRefs(forecastStore);

watch(
  editForecastSeriesId,
  (seriesId) => {
    if (seriesId && seriesId === props.forecast.forecast_series_id) {
      showEditDialog.value = true;
      forecastStore.clearEditRequest();
    }
  },
  { immediate: true },
);

const categoryIcon = computed(() =>
  getIconComponent(props.forecast.category?.icon ?? null),
);

const formattedAmount = computed(() =>
  formatCentsToDisplay(props.forecast.amount),
);
const formattedSpent = computed(() =>
  formatCentsToDisplay(props.forecast.spent_to_date),
);
const formattedRemaining = computed(() =>
  formatCentsToDisplay(props.forecast.remaining),
);
const formattedDaily = computed(() =>
  formatCentsToDisplay(props.forecast.daily_allowance),
);

const monthLabel = computed(() => formatMonth(props.forecast.month));

const progressPercent = computed(() => {
  if (props.forecast.amount <= 0) return 0;
  return Math.min(
    100,
    Math.round((props.forecast.spent_to_date / props.forecast.amount) * 100),
  );
});

const paceClass = computed(() => {
  switch (props.forecast.pace_status) {
    case 'over_pace':
      return 'bg-destructive/10 text-destructive border-destructive/30';
    case 'under_pace':
      return 'bg-positive/10 text-positive border-positive/30';
    default:
      return 'bg-muted text-muted-foreground';
  }
});

const progressBarClass = computed(() => {
  switch (props.forecast.pace_status) {
    case 'over_pace':
      return 'bg-destructive';
    case 'under_pace':
      return 'bg-positive';
    default:
      return 'bg-foreground/70';
  }
});
</script>

<template>
  <Card
    :class="
      cn(
        'gap-3 rounded-md bg-sidebar p-4 transition-colors',
        !forecast.is_active && 'opacity-60',
      )
    "
  >
    <!-- Header -->
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
          <component
            :is="categoryIcon"
            :size="16"
            class="shrink-0 text-muted-foreground"
          />
          <h3 class="truncate text-sm leading-tight font-semibold text-foreground">
            {{ forecast.category?.name }}
          </h3>
          <Badge
            v-if="!forecast.is_active"
            variant="secondary"
            class="px-1.5 py-0 text-xs"
          >
            {{ t('generic.labels.paused') }}
          </Badge>
          <Badge
            v-else
            variant="secondary"
            :class="cn('rounded-md border px-2 py-0.5 text-[11px] font-medium', paceClass)"
          >
            {{ t(`forecasts.pace.${forecast.pace_status}`) }}
          </Badge>
        </div>
        <div class="mt-1 text-xs text-muted-foreground">
          {{ monthLabel }}
        </div>
      </div>

      <div class="flex shrink-0 items-center gap-1">
        <ActionGroup>
          <EditButton @click="showEditDialog = true" />
          <ToggleActiveButton
            :toggle-url="toggleForecast(forecast.id!).url"
            :is-active="forecast.is_active"
          />
          <DeleteButton @click="showDeleteDialog = true" />
        </ActionGroup>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="space-y-1.5">
      <div class="flex items-baseline justify-between text-xs">
        <span class="font-medium text-foreground">
          {{ getCurrencySymbol() }} {{ formattedSpent }}
          <span class="font-normal text-muted-foreground">
            {{ t('forecasts.card.ofTotal', { total: `${getCurrencySymbol()} ${formattedAmount}` }) }}
          </span>
        </span>
        <span class="text-muted-foreground">{{ progressPercent }}%</span>
      </div>
      <div class="h-1.5 w-full overflow-hidden rounded-full bg-foreground/10">
        <div
          :class="cn('h-full transition-all', progressBarClass)"
          :style="{ width: `${progressPercent}%` }"
        />
      </div>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-3 gap-3 text-xs">
      <div>
        <div class="font-medium text-muted-foreground/70">
          {{ t('forecasts.card.spent') }}
        </div>
        <div class="font-semibold text-foreground">
          {{ getCurrencySymbol() }} {{ formattedSpent }}
        </div>
      </div>
      <div>
        <div class="font-medium text-muted-foreground/70">
          {{ t('forecasts.card.remaining') }}
        </div>
        <div class="font-semibold text-foreground">
          {{ getCurrencySymbol() }} {{ formattedRemaining }}
        </div>
      </div>
      <div>
        <div class="font-medium text-muted-foreground/70">
          {{ t('forecasts.card.dailyAllowance') }}
        </div>
        <div class="font-semibold text-foreground">
          {{ getCurrencySymbol() }} {{ formattedDaily }}
        </div>
      </div>
    </div>
  </Card>

  <EditForecastDialog
    v-if="showEditDialog"
    v-model:open="showEditDialog"
    :forecast="forecast"
    :categories="categories"
  />

  <DeleteForecastDialog
    v-if="showDeleteDialog"
    v-model:open="showDeleteDialog"
    :forecast="forecast"
  />
</template>
