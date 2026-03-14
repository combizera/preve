<script setup lang="ts">
import { computed } from 'vue';

import { formatCentsToDisplay } from '@/lib/currency';
import { cn } from '@/lib/utils';

const props = withDefaults(
  defineProps<{
    payload?: Record<string, any>;
    config?: Record<string, any>;
    x?: number | Date;
    monthName?: string;
  }>(),
  {
    payload: () => ({}),
    config: () => ({}),
    monthName: '',
  },
);

const tooltipLabel = computed(() => {
  const day = props.payload?.day;
  if (day && props.monthName) {
    return `${props.monthName} ${day}`;
  }
  return '';
});

const items = computed(() => {
  return Object.entries(props.payload)
    .map(([key, value]) => {
      const itemConfig = props.config[key];
      if (!itemConfig) return null;
      const numValue = typeof value === 'number' ? value : 0;
      const isNegative = numValue < 0;
      const formatted = `R$ ${formatCentsToDisplay(Math.abs(numValue))}`;
      const display = isNegative ? `-${formatted}` : formatted;
      const dotColor = isNegative ? 'var(--destructive)' : 'var(--positive)';
      return { key, display, label: itemConfig.label, dotColor };
    })
    .filter(Boolean);
});
</script>

<template>
  <div
    :class="
      cn(
        'border-border/50 bg-background grid min-w-[10rem] items-start gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl',
      )
    "
  >
    <div v-if="tooltipLabel" class="font-medium">
      {{ tooltipLabel }}
    </div>
    <div class="grid gap-1.5">
      <div
        v-for="item in items"
        :key="item!.key"
        class="flex w-full items-center gap-2"
      >
        <div
          class="size-2.5 shrink-0 rounded-[2px]"
          :style="{ backgroundColor: item!.dotColor }"
        />
        <div class="flex flex-1 items-center justify-between gap-4 leading-none">
          <span class="text-muted-foreground">
            {{ item!.label }}
          </span>
          <span class="text-foreground font-mono font-medium tabular-nums">
            {{ item!.display }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
