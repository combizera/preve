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

interface Props {
  monthlyIncome: number;
  monthlyExpenses: number;
  selectedMonth: { month: number; year: number } | null;
}

const props = defineProps<Props>();

const now = new Date();
const TODAY = now.getDate();

const displayMonth = computed(() => {
  const month = props.selectedMonth?.month ?? now.getMonth() + 1;
  const year = props.selectedMonth?.year ?? now.getFullYear();
  return `${MONTHS[month - 1]} ${year}`;
});

// Mock: daily transactions (chart data will come from backend next)
const dailyTransactions = [
  { day: 1, amount: -150000 },
  { day: 2, amount: -85000 },
  { day: 3, amount: -32000 },
  { day: 4, amount: -18000 },
  { day: 5, amount: 450000 },
  { day: 6, amount: 0 },
  { day: 7, amount: -25000 },
  { day: 8, amount: -15000 },
  { day: 9, amount: 0 },
  { day: 10, amount: -42000 },
  { day: 11, amount: -8000 },
  { day: 12, amount: 0 },
  { day: 13, amount: -22000 },
  { day: 14, amount: -12000 },
  { day: 15, amount: 0 },
  { day: 16, amount: -18000 },
  { day: 17, amount: -9000 },
  { day: 18, amount: 0 },
  { day: 19, amount: -15000 },
  { day: 20, amount: 120000 },
  { day: 21, amount: 0 },
  { day: 22, amount: -28000 },
  { day: 23, amount: 0 },
  { day: 24, amount: -19000 },
  { day: 25, amount: 0 },
  { day: 26, amount: -12000 },
  { day: 27, amount: 0 },
  { day: 28, amount: -8000 },
  { day: 29, amount: 0 },
  { day: 30, amount: -11000 },
  { day: 31, amount: 0 },
];

type ChartPoint = { day: number; balance: number };

// Accumulated balance per day
const chartData = computed(() => {
  let acc = 0;
  return dailyTransactions.map((d) => {
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
const yActual = (d: ChartPoint) => (d.day <= TODAY ? d.balance / 100 : undefined);

// Forecast: from today onwards (shares today's point to connect)
const yForecast = (d: ChartPoint) => (d.day >= TODAY ? d.balance / 100 : undefined);

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
                labelFormatter: (i: number | Date) => {
                  const idx = Math.round(Number(i));
                  const data = chartData.value;
                  if (!data || idx < 0 || idx >= data.length) return '';
                  const point = data[idx];
                  return point.day <= TODAY ? `Day ${point.day}` : `Day ${point.day} (forecast)`;
                },
              })
            "
          />
        </VisXYContainer>
      </ChartContainer>
    </div>
  </section>
</template>
