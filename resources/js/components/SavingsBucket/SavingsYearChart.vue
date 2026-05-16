<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import SavingsYearChartTooltip from '@/components/SavingsBucket/SavingsYearChartTooltip.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { ChartConfig } from '@/components/ui/chart';
import {
  ChartContainer,
  ChartCrosshair,
  ChartTooltip,
  componentToString,
} from '@/components/ui/chart';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { MONTH_KEYS } from '@/lib/calendar';
import { formatCompactCurrency } from '@/lib/currency';
import type { ISavingsMonth } from '@/types/models/savings-history';

interface Props {
  chartData: ISavingsMonth[];
  availableYears: number[];
  selectedYear: number;
}

const props = defineProps<Props>();

const { t } = useI18n();

type ChartPoint = ISavingsMonth & { index: number };

const points = computed<ChartPoint[]>(() =>
  props.chartData.map((row, index) => ({ ...row, index })),
);

const monthLabels = computed(() =>
  MONTH_KEYS.map((key) => t(`dashboard.calendar.months.${key}`)),
);

const chartConfig = computed<ChartConfig>(() => ({
  balance: {
    label: t('savings.chart.balance'),
    color: 'var(--primary)',
  },
  deposits: {
    label: t('savings.chart.deposits'),
    color: 'var(--positive)',
  },
  withdrawals: {
    label: t('savings.chart.withdrawals'),
    color: 'var(--destructive)',
  },
}));

const x = (_d: ChartPoint, i: number) => i;
const yBalance = (d: ChartPoint) => d.balance;
const tickFormat = (i: number) => monthLabels.value[i]?.slice(0, 3) ?? '';

const onYearChange = (next: string) => {
  const year = Number(next);
  if (!Number.isFinite(year) || year === props.selectedYear) return;
  router.reload({ data: { year }, only: ['chartData', 'selectedYear'] });
};
</script>

<template>
  <Card class="gap-0">
    <CardHeader
      class="flex flex-row items-center justify-between gap-4 space-y-0"
    >
      <CardTitle class="text-sm font-medium text-muted-foreground">
        {{ t('savings.chart.title') }}
      </CardTitle>
      <Select
        :model-value="String(selectedYear)"
        @update:model-value="onYearChange"
      >
        <SelectTrigger class="h-8 w-[110px]">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem
            v-for="year in availableYears"
            :key="year"
            :value="String(year)"
          >
            {{ year }}
          </SelectItem>
        </SelectContent>
      </Select>
    </CardHeader>
    <CardContent class="px-2 pt-4 pb-2">
      <ChartContainer
        :config="chartConfig"
        class="h-[260px] w-full"
        :cursor="true"
      >
        <VisXYContainer :data="points" :padding="{ top: 20, bottom: 20 }">
          <VisArea
            :x="x"
            :y="yBalance"
            :opacity="0.1"
            :color="chartConfig.balance.color"
            :curve-type="CurveType.MonotoneX"
          />
          <VisLine
            :x="x"
            :y="yBalance"
            :color="chartConfig.balance.color"
            :curve-type="CurveType.MonotoneX"
            :line-width="2"
          />
          <VisAxis
            type="x"
            :x="x"
            :tick-format="tickFormat"
            :tick-line="false"
            :domain-line="false"
            :grid-line="false"
            :num-ticks="12"
          />
          <VisAxis
            type="y"
            :tick-format="formatCompactCurrency"
            :tick-line="false"
            :domain-line="false"
            :grid-line="true"
          />
          <ChartTooltip />
          <ChartCrosshair
            :color="chartConfig.balance.color"
            :template="
              componentToString(chartConfig, SavingsYearChartTooltip, {
                monthLabels: monthLabels,
              })
            "
          />
        </VisXYContainer>
      </ChartContainer>
    </CardContent>
  </Card>
</template>
