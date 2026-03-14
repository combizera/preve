<script setup lang="ts">
import { TrendingUp, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import DashboardCard from '@/components/Dashboard/DashboardCard.vue';
import { MONTHS } from '@/lib/calendar';
import { formatCentsToDisplay } from '@/lib/currency';

const { t } = useI18n();

interface Props {
  availableBalance: number;
  forecast: number;
  selectedMonth: { month: number; year: number } | null;
}

const props = defineProps<Props>();

const balanceVariant = computed(() => {
  if (props.availableBalance > 0) return 'positive';
  if (props.availableBalance < 0) return 'destructive';
  return 'neutral';
});

const forecastVariant = computed(() => {
  if (props.forecast > 0) return 'positive';
  if (props.forecast < 0) return 'destructive';
  return 'neutral';
});

const forecastDescription = computed(() => {
  if (!props.selectedMonth) return t('dashboard.forecastDescription');
  return t('dashboard.forecastForMonth', {
    month: MONTHS[props.selectedMonth.month - 1],
    year: props.selectedMonth.year,
  });
});
</script>

<template>
  <div class="grid grid-cols-2 gap-4">
    <DashboardCard
      :title="t('dashboard.availableBalance')"
      :description="t('dashboard.availableBalanceDescription')"
      :amount="formatCentsToDisplay(availableBalance)"
      :variant="balanceVariant"
    >
      <template #icon>
        <Wallet :size="16" />
      </template>
    </DashboardCard>

    <DashboardCard
      :title="t('dashboard.forecast')"
      :description="forecastDescription"
      :amount="formatCentsToDisplay(forecast)"
      :variant="forecastVariant"
    >
      <template #icon>
        <TrendingUp :size="16" />
      </template>
    </DashboardCard>
  </div>
</template>
