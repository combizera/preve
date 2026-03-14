<script setup lang="ts">
import type { ChartConfig } from '@/components/ui/chart';
import {
  ChartContainer,
  ChartCrosshair,
  ChartTooltip,
  componentToString,
} from '@/components/ui/chart';
import ChartMonthlyTooltip from '@/components/Dashboard/ChartMonthlyTooltip.vue';
import { formatCentsToDisplay } from '@/lib/currency';
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';

const TODAY = 14;

// Mock: daily transactions for March 2026
const dailyTransactions = [
  { day: 1, amount: -85000 },
  { day: 2, amount: -120000 },
  { day: 3, amount: -45000 },
  { day: 4, amount: -18000 },
  { day: 5, amount: 450000 },
  { day: 6, amount: 0 },
  { day: 7, amount: -32000 },
  { day: 8, amount: -15000 },
  { day: 9, amount: 0 },
  { day: 10, amount: -62000 },
  { day: 11, amount: -28000 },
  { day: 12, amount: 0 },
  { day: 13, amount: -35000 },
  { day: 14, amount: -22000 },
  { day: 15, amount: 0 },
  { day: 16, amount: 0 },
  { day: 17, amount: -41000 },
  { day: 18, amount: 0 },
  { day: 19, amount: -19000 },
  { day: 20, amount: 350000 },
  { day: 21, amount: 0 },
  { day: 22, amount: -55000 },
  { day: 23, amount: 0 },
  { day: 24, amount: -33000 },
  { day: 25, amount: 0 },
  { day: 26, amount: -27000 },
  { day: 27, amount: 0 },
  { day: 28, amount: -12000 },
  { day: 29, amount: 0 },
  { day: 30, amount: -15000 },
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

const totalIncome = computed(() =>
  dailyTransactions.filter((d) => d.amount > 0).reduce((sum, d) => sum + d.amount, 0),
);
const totalExpense = computed(() =>
  dailyTransactions.filter((d) => d.amount < 0).reduce((sum, d) => sum + Math.abs(d.amount), 0),
);

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
  if (d === 0) return '0';
  const formatted = formatCentsToDisplay(Math.abs(d) * 100);
  return d < 0 ? `-R$ ${formatted}` : `R$ ${formatted}`;
};
</script>

<template>
  <section class="double-border">
    <div class="flex flex-col border rounded-lg overflow-hidden">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row">
        <div class="flex flex-1 flex-col justify-center gap-1 px-6 py-4">
          <h3 class="text-lg font-medium text-foreground">Daily Balance</h3>
          <p class="text-sm text-muted-foreground">March 2026</p>
        </div>
        <div class="flex">
          <div class="flex flex-1 flex-col justify-center gap-1 border-t sm:border-t-0 sm:border-l px-6 py-4">
            <span class="text-sm font-medium text-muted-foreground">Income</span>
            <span class="text-lg font-bold font-mono text-positive">
              R$ {{ formatCentsToDisplay(totalIncome) }}
            </span>
          </div>
          <div class="flex flex-1 flex-col justify-center gap-1 border-t border-l sm:border-t-0 px-6 py-4">
            <span class="text-sm font-medium text-muted-foreground">Expenses</span>
            <span class="text-lg font-bold font-mono text-destructive">
              R$ {{ formatCentsToDisplay(totalExpense) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Chart -->
      <ChartContainer :config="chartConfig" class="h-[300px] w-full border-t px-2 pt-4 pb-2" :cursor="true">
        <VisXYContainer :data="chartData" :padding="{ top: 10 }">
          <!-- Actual: solid area + line -->
          <VisArea
            :x="x"
            :y="yActual"
            :opacity="0.08"
            :color="chartConfig.balance.color"
            :curve-type="CurveType.Step"
          />
          <VisLine
            :x="x"
            :y="yActual"
            :color="chartConfig.balance.color"
            :curve-type="CurveType.Step"
            :line-width="2"
          />

          <!-- Forecast: dashed line + subtle area -->
          <VisArea
            :x="x"
            :y="yForecast"
            :opacity="0.04"
            :color="chartConfig.forecast.color"
            :curve-type="CurveType.Step"
          />
          <VisLine
            :x="x"
            :y="yForecast"
            :color="chartConfig.forecast.color"
            :curve-type="CurveType.Step"
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
