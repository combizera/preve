<script setup lang="ts">
import { TrendingUp, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

import DashboardCard from '@/components/Dashboard/DashboardCard.vue';
import { MONTHS } from '@/lib/calendar';
import { formatCentsToDisplay } from '@/lib/currency';

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
  if (!props.selectedMonth) return 'Balance after pending expenses';
  return `Forecast for ${MONTHS[props.selectedMonth.month - 1]} ${props.selectedMonth.year}`;
});
</script>

<template>
  <div class="grid grid-cols-2 gap-4">
    <DashboardCard
      title="Available Balance"
      description="Income minus paid expenses this month"
      :amount="formatCentsToDisplay(availableBalance)"
      :variant="balanceVariant"
    >
      <template #icon>
        <Wallet :size="16" />
      </template>
    </DashboardCard>

    <DashboardCard
      title="Forecast"
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
