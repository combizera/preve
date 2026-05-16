<script setup lang="ts">
import { computed } from 'vue';

import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { cn } from '@/lib/utils';

const props = withDefaults(
  defineProps<{
    payload?: Record<string, any>;
    config?: Record<string, any>;
    monthLabels?: string[];
  }>(),
  {
    payload: () => ({}),
    config: () => ({}),
    monthLabels: () => [],
  },
);

const tooltipLabel = computed(() => {
  const index = props.payload?.index;
  if (typeof index !== 'number') return '';
  return props.monthLabels[index] ?? '';
});

type Row = {
  key: string;
  label: string;
  display: string;
  dotColor: string;
};

const rows = computed<Row[]>(() => {
  const out: Row[] = [];

  for (const key of ['balance', 'deposits', 'withdrawals']) {
    const itemConfig = props.config[key];
    const raw = props.payload?.[key];
    if (!itemConfig || typeof raw !== 'number') continue;

    out.push({
      key,
      label: itemConfig.label,
      display: `${getCurrencySymbol()} ${formatCentsToDisplay(raw)}`,
      dotColor: itemConfig.color,
    });
  }

  return out;
});
</script>

<template>
  <div
    :class="
      cn(
        'grid min-w-[10rem] items-start gap-1.5 rounded-lg border border-border/50 bg-background px-2.5 py-1.5 text-xs shadow-xl',
      )
    "
  >
    <div v-if="tooltipLabel" class="font-medium">
      {{ tooltipLabel }}
    </div>
    <div class="grid gap-1.5">
      <div
        v-for="row in rows"
        :key="row.key"
        class="flex w-full items-center gap-2"
      >
        <div
          class="size-2.5 shrink-0 rounded-[2px]"
          :style="{ backgroundColor: row.dotColor }"
        />
        <div
          class="flex flex-1 items-center justify-between gap-4 leading-none"
        >
          <span class="text-muted-foreground">{{ row.label }}</span>
          <span class="font-mono font-medium text-foreground tabular-nums">
            {{ row.display }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
