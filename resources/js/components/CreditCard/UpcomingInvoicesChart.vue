<script setup lang="ts">
import { VisAxis, VisStackedBar, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { ChartConfig } from '@/components/ui/chart';
import { ChartContainer } from '@/components/ui/chart';
import { MONTH_KEYS } from '@/lib/calendar';
import { getCreditCardChartColor } from '@/lib/credit-card-colors';
import { formatCompactCurrency } from '@/lib/currency';
import type {
  ICreditCard,
  IUpcomingInvoiceMonth,
} from '@/types/models/credit-card';

interface Props {
  creditCards: ICreditCard[];
  upcomingInvoices: IUpcomingInvoiceMonth[];
}

const props = defineProps<Props>();

const { t } = useI18n();

type ChartPoint = IUpcomingInvoiceMonth & { index: number };

const points = computed<ChartPoint[]>(() =>
  props.upcomingInvoices.map((row, index) => ({ ...row, index })),
);

const hasUpcoming = computed(() =>
  props.upcomingInvoices.some((row) =>
    Object.values(row.totals).some((amount) => amount > 0),
  ),
);

const chartConfig = computed<ChartConfig>(() =>
  Object.fromEntries(
    props.creditCards.map((card) => [
      `card_${card.id}`,
      { label: card.name, color: getCreditCardChartColor(card.color) },
    ]),
  ),
);

const x = (_d: ChartPoint, i: number) => i;

const yAccessors = computed(() =>
  props.creditCards.map((card) => (d: ChartPoint) => d.totals[card.id] ?? 0),
);

const colors = computed(() =>
  props.creditCards.map((card) => getCreditCardChartColor(card.color)),
);

const tickFormat = (i: number) => {
  const point = points.value[i];
  if (!point) return '';
  return t(`dashboard.calendar.months.${MONTH_KEYS[point.month - 1]}`).slice(
    0,
    3,
  );
};
</script>

<template>
  <Card class="gap-0">
    <CardHeader>
      <CardTitle class="text-sm font-medium text-muted-foreground">
        {{ t('creditCards.chart.title') }}
      </CardTitle>
    </CardHeader>
    <CardContent class="px-2 pt-4 pb-2">
      <p
        v-if="!hasUpcoming"
        class="py-12 text-center text-sm text-muted-foreground"
      >
        {{ t('creditCards.chart.empty') }}
      </p>

      <template v-else>
        <div class="mb-4 flex flex-wrap gap-x-4 gap-y-2 px-2">
          <div
            v-for="card in creditCards"
            :key="card.id"
            class="flex items-center gap-1.5 text-xs text-muted-foreground"
          >
            <span
              class="size-2.5 rounded-full"
              :style="{ backgroundColor: getCreditCardChartColor(card.color) }"
            />
            {{ card.name }}
          </div>
        </div>

        <ChartContainer :config="chartConfig" class="h-[260px] w-full">
          <VisXYContainer :data="points" :padding="{ top: 20 }">
            <VisStackedBar
              :x="x"
              :y="yAccessors"
              :color="colors"
              :rounded-corners="4"
              :bar-padding="0.3"
            />
            <VisAxis
              type="x"
              :x="x"
              :tick-format="tickFormat"
              :tick-line="false"
              :domain-line="false"
              :grid-line="false"
              :num-ticks="points.length"
            />
            <VisAxis
              type="y"
              :tick-format="formatCompactCurrency"
              :tick-line="false"
              :domain-line="false"
              :grid-line="true"
            />
          </VisXYContainer>
        </ChartContainer>
      </template>
    </CardContent>
  </Card>
</template>
