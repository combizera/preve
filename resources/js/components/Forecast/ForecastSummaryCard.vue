<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import ForecastProgressBar from '@/components/Forecast/ForecastProgressBar.vue';
import ForecastStatsGrid from '@/components/Forecast/ForecastStatsGrid.vue';
import { Card } from '@/components/ui/card';
import { getCurrentMonthString, summarizeForecasts } from '@/lib/forecast';
import type { IForecast } from '@/types/models/forecast';
import { formatMonth } from '@/utils/formatDate';

const props = defineProps<{
  forecasts: IForecast[];
}>();

const { t } = useI18n();

const currentMonth = computed(() => getCurrentMonthString());
const monthLabel = computed(() => formatMonth(currentMonth.value));
const totals = computed(() => summarizeForecasts(props.forecasts, currentMonth.value));
</script>

<template>
  <Card v-if="totals.activeCount > 0" class="gap-4 rounded-md border-2 bg-sidebar p-5">
    <div class="flex items-baseline justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-foreground">
          {{ t('forecasts.summary.title') }}
        </h2>
        <p class="mt-0.5 text-xs text-muted-foreground">
          {{ t('forecasts.summary.subtitle', { count: totals.activeCount }) }}
        </p>
      </div>
      <span class="text-xs font-medium text-muted-foreground">{{ monthLabel }}</span>
    </div>

    <ForecastProgressBar
      :spent="totals.spent"
      :amount="totals.amount"
      :month="currentMonth"
      :pace-status="totals.paceStatus"
    />

    <ForecastStatsGrid
      :spent="totals.spent"
      :remaining="totals.remaining"
      :daily-allowance="totals.dailyAllowance"
    />
  </Card>
</template>
