<script setup lang="ts">
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import ChartHeader from '@/components/Dashboard/ChartHeader.vue';
import ChartMonthlyTooltip from '@/components/Dashboard/ChartMonthlyTooltip.vue';
import type { ChartConfig } from '@/components/ui/chart';
import {
  ChartContainer,
  ChartCrosshair,
  ChartTooltip,
  componentToString,
} from '@/components/ui/chart';
import { MONTH_KEYS } from '@/lib/calendar';
import { formatCompactCurrency } from '@/lib/currency';
import type { IDailyBalance } from '@/types/models/transaction';

const { t } = useI18n();

interface Props {
  monthlyIncome: number;
  monthlyExpenses: number;
  dailyBalances: IDailyBalance[];
  dailyForecastedSpend: number;
  carryOver: number;
  selectedMonth: { month: number; year: number } | null;
}

const props = defineProps<Props>();

const now = new Date();

const currentDay = computed(() => {
  const selectedMonth = props.selectedMonth;
  if (!selectedMonth) return now.getDate();

  const isCurrentMonth =
    selectedMonth.month === now.getMonth() + 1 && selectedMonth.year === now.getFullYear();
  if (isCurrentMonth) return now.getDate();

  const isPastMonth =
    selectedMonth.year < now.getFullYear() ||
    (selectedMonth.year === now.getFullYear() && selectedMonth.month < now.getMonth() + 1);

  return isPastMonth ? 32 : 0;
});

const selectedMonthIndex = computed(() => (props.selectedMonth?.month ?? now.getMonth() + 1) - 1);

const monthName = computed(() => t(`dashboard.calendar.months.${MONTH_KEYS[selectedMonthIndex.value]}`));

const displayMonth = computed(() => {
  const year = props.selectedMonth?.year ?? now.getFullYear();
  return `${monthName.value} ${year}`;
});

type ChartPoint = { day: number; balance: number };

const chartData = computed(() => {
  let acc = props.carryOver;
  return props.dailyBalances.map((d) => {
    acc += d.amount;
    const daysIntoFuture = Math.max(0, d.day - currentDay.value);
    const balance = acc - props.dailyForecastedSpend * daysIntoFuture;
    return { day: d.day, balance };
  });
});

const chartConfig = computed<ChartConfig>(() => ({
  balance: {
    label: t('dashboard.chart.balance'),
    color: 'var(--primary)',
  },
  forecast: {
    label: t('dashboard.chart.forecast'),
    color: 'var(--primary)',
  },
}));

const x = (_d: ChartPoint, i: number) => i;

const yActual = (d: ChartPoint) => (d.day <= currentDay.value ? d.balance : undefined);

const yForecast = (d: ChartPoint) => (d.day >= currentDay.value ? d.balance : undefined);

const formatDay = (i: number) => {
  const point = chartData.value[i];
  return point ? String(point.day) : '';
};
</script>

<template>
  <section class="double-border">
    <div class="flex flex-col border rounded-lg overflow-hidden">
      <ChartHeader
        :title="t('dashboard.chart.dailyBalance')"
        :description="displayMonth"
        :items="[
          { label: t('dashboard.chart.income'), value: props.monthlyIncome, variant: 'positive' },
          { label: t('dashboard.chart.expenses'), value: props.monthlyExpenses, variant: 'destructive' },
        ]"
      />

      <!-- CHART -->
      <ChartContainer :config="chartConfig" class="h-[300px] w-full border-t px-2 pt-4 pb-2" :cursor="true">
        <VisXYContainer :data="chartData" :padding="{ top: 20, bottom: 20 }">
          <!-- ACTUAL -->
          <VisArea
            :x="x"
            :y="yActual"
            :opacity="0.08"
            :color="chartConfig.balance.color"
            :curve-type="CurveType.MonotoneX"
          />
          <VisLine
            :x="x"
            :y="yActual"
            :color="chartConfig.balance.color"
            :curve-type="CurveType.MonotoneX"
            :line-width="2"
          />

          <!-- FORECAST -->
          <VisArea
            :x="x"
            :y="yForecast"
            :opacity="0.04"
            :color="chartConfig.forecast.color"
            :curve-type="CurveType.MonotoneX"
          />
          <VisLine
            :x="x"
            :y="yForecast"
            :color="chartConfig.forecast.color"
            :curve-type="CurveType.MonotoneX"
            :line-width="2"
            :line-dash-array="[6, 4]"
          />

          <!-- AXES -->
          <VisAxis
            type="x"
            :x="x"
            :tick-format="formatDay"
            :tick-line="false"
            :domain-line="false"
            :grid-line="false"
            :num-ticks="10"
          />
          <VisAxis
            type="y"
            :tick-format="formatCompactCurrency"
            :tick-line="false"
            :domain-line="false"
            :grid-line="true"
          />

          <!-- TOOLTIP -->
          <ChartTooltip />
          <ChartCrosshair
            :color="chartConfig.balance.color"
            :template="
              componentToString(chartConfig, ChartMonthlyTooltip, {
                monthName: monthName,
              })
            "
          />
        </VisXYContainer>
      </ChartContainer>
    </div>
  </section>
</template>
