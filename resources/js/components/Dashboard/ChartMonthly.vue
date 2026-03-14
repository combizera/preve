<script setup lang="ts">
import type { ChartConfig } from '@/components/ui/chart';
import {
  ChartContainer,
  ChartCrosshair,
  ChartTooltip,
  componentToString,
} from '@/components/ui/chart';
import ChartHeader from '@/components/Dashboard/ChartHeader.vue';
import ChartMonthlyTooltip from '@/components/Dashboard/ChartMonthlyTooltip.vue';
import { MONTHS } from '@/lib/calendar';
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

interface DailyBalance {
  day: number;
  amount: number;
}

interface Props {
  monthlyIncome: number;
  monthlyExpenses: number;
  dailyBalances: DailyBalance[];
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

  // Past month: all actual (day 32 means all days <= 31 are actual)
  // Future month: all forecast (day 0 means no days are actual)
  return isPastMonth ? 32 : 0;
});

const displayMonth = computed(() => {
  const month = props.selectedMonth?.month ?? now.getMonth() + 1;
  const year = props.selectedMonth?.year ?? now.getFullYear();
  return `${MONTHS[month - 1]} ${year}`;
});


type ChartPoint = { day: number; balance: number };

// Accumulated balance per day
const chartData = computed(() => {
  let acc = 0;
  return props.dailyBalances.map((d) => {
    acc += d.amount;
    return { day: d.day, balance: acc };
  });
});

const chartConfig = {
  balance: {
    label: 'Balance',
    color: 'var(--primary)',
  },
  forecast: {
    label: 'Forecast',
    color: 'var(--primary)',
  },
} satisfies ChartConfig;

const x = (_d: ChartPoint, i: number) => i;

// Actual: up to and including today
const yActual = (d: ChartPoint) => (d.day <= currentDay.value ? d.balance / 100 : undefined);

// Forecast: from today onwards (shares today's point to connect)
const yForecast = (d: ChartPoint) => (d.day >= currentDay.value ? d.balance / 100 : undefined);

const formatDay = (i: number) => {
  const point = chartData.value[i];
  return point ? String(point.day) : '';
};

const formatCurrency = (d: number) => {
  if (d === 0) return 'R$ 0';
  const abs = Math.abs(d);
  const sign = d < 0 ? '-' : '';
  if (abs >= 1000) return `${sign}R$ ${(abs / 1000).toFixed(0)}k`;
  return `${sign}R$ ${abs.toFixed(0)}`;
};
</script>

<template>
  <section class="double-border">
    <div class="flex flex-col border rounded-lg overflow-hidden">
      <ChartHeader
        title="Daily Balance"
        :description="displayMonth"
        :items="[
          { label: 'Income', value: props.monthlyIncome, variant: 'positive' },
          { label: 'Expenses', value: props.monthlyExpenses, variant: 'destructive' },
        ]"
      />

      <!-- Chart -->
      <ChartContainer :config="chartConfig" class="h-[300px] w-full border-t px-2 pt-4 pb-2" :cursor="true">
        <VisXYContainer :data="chartData" :padding="{ top: 20, bottom: 20 }">
          <!-- Actual: solid area + line -->
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

          <!-- Forecast: dashed line + subtle area -->
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

          <!-- Axes -->
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
            :tick-format="formatCurrency"
            :tick-line="false"
            :domain-line="false"
            :grid-line="true"
          />

          <!-- Tooltip -->
          <ChartTooltip />
          <ChartCrosshair
            :color="chartConfig.balance.color"
            :template="
              componentToString(chartConfig, ChartMonthlyTooltip, {
                monthName: MONTHS[(props.selectedMonth?.month ?? now.getMonth() + 1) - 1],
              })
            "
          />
        </VisXYContainer>
      </ChartContainer>
    </div>
  </section>
</template>
