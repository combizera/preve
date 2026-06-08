<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';

const props = defineProps<{
  spent: number;
  remaining: number;
  dailyAllowance: number;
}>();

const { t } = useI18n();

const stats = computed(() => [
  { label: t('forecasts.card.spent'), value: props.spent },
  { label: t('forecasts.card.remaining'), value: props.remaining },
  { label: t('forecasts.card.dailyAllowance'), value: props.dailyAllowance },
]);
</script>

<template>
  <div class="grid grid-cols-3 gap-3 text-xs">
    <div v-for="stat in stats" :key="stat.label">
      <div class="font-medium text-muted-foreground/70">{{ stat.label }}</div>
      <div class="font-semibold text-foreground">
        {{ getCurrencySymbol() }} {{ formatCentsToDisplay(stat.value) }}
      </div>
    </div>
  </div>
</template>
