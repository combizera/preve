<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import {
  getMonthProgressPercent,
  getPaceClasses,
  getProgressPercent,
} from '@/lib/forecast';
import { cn } from '@/lib/utils';
import type { ForecastPaceStatus } from '@/types/models/forecast';

const props = defineProps<{
  spent: number;
  amount: number;
  month: string;
  paceStatus: ForecastPaceStatus;
}>();

const { t } = useI18n();

const percent = computed(() => getProgressPercent(props.spent, props.amount));
const pacePercent = computed(() => getMonthProgressPercent(props.month));
const barClass = computed(() => getPaceClasses(props.paceStatus).bar);

const totalLabel = computed(
  () => `${getCurrencySymbol()} ${formatCentsToDisplay(props.amount)}`,
);
const spentLabel = computed(
  () => `${getCurrencySymbol()} ${formatCentsToDisplay(props.spent)}`,
);
</script>

<template>
  <div class="space-y-1.5">
    <div class="flex items-baseline justify-between text-xs">
      <span class="font-medium text-foreground">
        {{ spentLabel }}
        <span class="font-normal text-muted-foreground">
          {{ t('forecasts.card.ofTotal', { total: totalLabel }) }}
        </span>
      </span>
      <span class="text-muted-foreground">{{ percent }}%</span>
    </div>
    <div class="relative h-1.5 w-full overflow-hidden rounded-full bg-foreground/10">
      <div
        :class="cn('h-full transition-all', barClass)"
        :style="{ width: `${percent}%` }"
      />
      <div
        class="absolute top-0 h-full w-0.5 -translate-x-1/2 bg-foreground/60"
        :style="{ left: `${pacePercent}%` }"
        :title="t('forecasts.card.expectedByNow', { percent: pacePercent })"
      />
    </div>
  </div>
</template>
