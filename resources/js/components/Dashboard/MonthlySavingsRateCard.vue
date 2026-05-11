<script setup lang="ts">
import { PiggyBank } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';

interface SavingsRate {
  deposits: number;
  income: number;
  rate: number | null;
}

const props = defineProps<{
  savingsRate: SavingsRate;
}>();

const { t } = useI18n();

const displayRate = computed(() =>
  props.savingsRate.rate === null
    ? '—'
    : `${props.savingsRate.rate.toFixed(props.savingsRate.rate % 1 === 0 ? 0 : 1)}%`,
);

const rateClass = computed(() => {
  const { rate } = props.savingsRate;
  if (rate === null || rate <= 0) return 'text-destructive';
  if (rate >= 10) return 'text-positive';
  return 'text-foreground';
});

const helperText = computed(() => {
  if (props.savingsRate.rate === null) {
    return t('dashboard.savingsRate.noIncome');
  }

  return t('dashboard.savingsRate.detail', {
    deposits: `${getCurrencySymbol()} ${formatCentsToDisplay(props.savingsRate.deposits)}`,
    income: `${getCurrencySymbol()} ${formatCentsToDisplay(props.savingsRate.income)}`,
  });
});
</script>

<template>
  <Card class="gap-0">
    <CardHeader>
      <CardTitle
        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
      >
        <PiggyBank :size="16" />
        {{ t('dashboard.savingsRate.title') }}
      </CardTitle>
    </CardHeader>
    <CardContent>
      <p class="font-mono text-2xl font-bold" :class="rateClass">
        {{ displayRate }}
      </p>
      <p class="mt-1 text-xs text-muted-foreground">
        {{ helperText }}
      </p>
    </CardContent>
  </Card>
</template>
