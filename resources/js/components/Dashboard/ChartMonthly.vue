<script setup lang="ts">
import type { ChartConfig } from '@/components/ui/chart';
import {
  ChartContainer,
  ChartCrosshair,
  ChartTooltip,
  ChartTooltipContent,
  componentToString,
} from '@/components/ui/chart';
import { formatCentsToDisplay } from '@/lib/currency';
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';

const chartData = [
  { month: 'Jan', income: 450000, expense: 320000 },
  { month: 'Fev', income: 480000, expense: 350000 },
  { month: 'Mar', income: 450000, expense: 410000 },
  { month: 'Abr', income: 520000, expense: 380000 },
  { month: 'Mai', income: 490000, expense: 420000 },
  { month: 'Jun', income: 550000, expense: 370000 },
  { month: 'Jul', income: 510000, expense: 390000 },
  { month: 'Ago', income: 530000, expense: 450000 },
  { month: 'Set', income: 480000, expense: 360000 },
  { month: 'Out', income: 560000, expense: 400000 },
  { month: 'Nov', income: 540000, expense: 430000 },
  { month: 'Dez', income: 600000, expense: 380000 },
];

type DataPoint = (typeof chartData)[number];

const chartConfig = {
  income: {
    label: 'Receitas',
    color: 'var(--positive)',
  },
  expense: {
    label: 'Despesas',
    color: 'var(--destructive)',
  },
} satisfies ChartConfig;

const x = (_d: DataPoint, i: number) => i;
const y = [
  (d: DataPoint) => d.income / 100,
  (d: DataPoint) => d.expense / 100,
];

const colors = [chartConfig.income.color, chartConfig.expense.color];

const formatMonth = (i: number) => chartData[i]?.month ?? '';

const formatCurrency = (d: number) => {
  if (d === 0) return '';
  return `R$ ${formatCentsToDisplay(d * 100)}`;
};
</script>

<template>
  <div class="flex flex-col gap-2 rounded-xl border p-4">
    <div class="flex items-center justify-between px-2">
      <div>
        <h3 class="text-sm font-medium">Receitas vs Despesas</h3>
        <p class="text-xs text-muted-foreground">Visão mensal do ano</p>
      </div>
    </div>

    <ChartContainer :config="chartConfig" class="min-h-[300px] w-full" :cursor="true">
      <VisXYContainer :data="chartData" :padding="{ top: 10 }">
        <VisArea
          :x="x"
          :y="y"
          :opacity="0.1"
          :color="colors"
          :curve-type="CurveType.MonotoneX"
        />

        <VisLine
          :x="x"
          :y="y"
          :color="colors"
          :curve-type="CurveType.MonotoneX"
          :line-width="2"
        />

        <VisAxis
          type="x"
          :x="x"
          :tick-format="formatMonth"
          :tick-line="false"
          :domain-line="false"
          :grid-line="false"
          :num-ticks="chartData.length"
        />
        <VisAxis
          type="y"
          :tick-format="formatCurrency"
          :tick-line="false"
          :domain-line="false"
          :grid-line="true"
        />

        <ChartTooltip />
        <ChartCrosshair
          :color="colors"
          :template="
            componentToString(chartConfig, ChartTooltipContent, {
              labelFormatter: (i: number | Date) => chartData[Number(i)]?.month ?? '',
              indicator: 'line' as const,
            })
          "
        />
      </VisXYContainer>
    </ChartContainer>
  </div>
</template>
