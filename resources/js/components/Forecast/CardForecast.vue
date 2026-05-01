<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteForecastDialog from '@/components/Forecast/DeleteForecastDialog.vue';
import EditForecastDialog from '@/components/Forecast/EditForecastDialog.vue';
import ForecastProgressBar from '@/components/Forecast/ForecastProgressBar.vue';
import ForecastStatsGrid from '@/components/Forecast/ForecastStatsGrid.vue';
import { Badge } from '@/components/ui/badge';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import ToggleActiveButton from '@/components/ui/button/ToggleActiveButton.vue';
import { Card } from '@/components/ui/card';
import { getIconComponent } from '@/lib/category-icons';
import { buildForecastTransactionsUrl, getPaceClasses } from '@/lib/forecast';
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
const monthLabel = computed(() => formatMonth(props.forecast.month));
const transactionsUrl = computed(() => buildForecastTransactionsUrl(props.forecast));
const paceBadgeClass = computed(() => getPaceClasses(props.forecast.pace_status).badge);
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
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
          <Link
            :href="transactionsUrl"
            class="flex min-w-0 items-center gap-2 rounded-sm hover:opacity-80 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            :title="t('forecasts.card.viewTransactions')"
          >
            <component
              :is="categoryIcon"
              :size="16"
              class="shrink-0 text-muted-foreground"
            />
            <h3 class="truncate text-sm leading-tight font-semibold text-foreground hover:underline">
              {{ forecast.category?.name }}
            </h3>
          </Link>
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
            :class="cn('rounded-md border px-2 py-0.5 text-[11px] font-medium', paceBadgeClass)"
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

    <ForecastProgressBar
      :spent="forecast.spent_to_date"
      :amount="forecast.amount"
      :pace-status="forecast.pace_status"
    />

    <ForecastStatsGrid
      :spent="forecast.spent_to_date"
      :remaining="forecast.remaining"
      :daily-allowance="forecast.daily_allowance"
    />
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
