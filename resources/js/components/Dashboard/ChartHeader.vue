<script setup lang="ts">
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';

interface ChartHeaderItem {
  label: string;
  value: number;
  variant: 'positive' | 'destructive' | 'neutral';
}

interface Props {
  title: string;
  description: string;
  items?: ChartHeaderItem[];
}

defineProps<Props>();

const variantClass: Record<ChartHeaderItem['variant'], string> = {
  positive: 'text-positive',
  destructive: 'text-destructive',
  neutral: 'text-foreground',
};
</script>

<template>
  <div class="flex flex-col sm:flex-row bg-sidebar">
    <div class="flex flex-1 flex-col justify-center gap-1 px-6 py-4">
      <h3 class="text-lg font-medium text-foreground">{{ title }}</h3>
      <p class="text-sm text-muted-foreground">{{ description }}</p>
    </div>
    <div v-if="items?.length" class="flex">
      <div
        v-for="(item, index) in items"
        :key="item.label"
        class="flex flex-1 flex-col justify-center gap-1 min-w-[150px] border-t sm:border-t-0 sm:border-l px-6 py-4"
        :class="{ 'border-l': index > 0 }"
      >
        <span class="text-sm font-medium text-muted-foreground">{{ item.label }}</span>
        <span class="text-lg font-bold font-mono" :class="variantClass[item.variant]">
          {{ getCurrencySymbol() }} {{ formatCentsToDisplay(item.value) }}
        </span>
      </div>
    </div>
  </div>
</template>
