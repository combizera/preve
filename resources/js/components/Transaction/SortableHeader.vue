<script setup lang="ts" generic="K extends string">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';

import { cn } from '@/lib/utils';

defineProps<{
  sortKey: K;
  activeKey: K | null;
  direction: 'asc' | 'desc';
  align?: 'left' | 'right';
}>();

const emit = defineEmits<{ sort: [key: K] }>();

const buttonClass =
  '-mx-2 inline-flex items-center gap-1 rounded px-2 py-1 transition-colors hover:bg-muted hover:text-foreground';
</script>

<template>
  <button
    type="button"
    :class="cn(buttonClass, align === 'right' && 'ml-auto')"
    @click="emit('sort', sortKey)"
  >
    <slot />
    <ArrowUp v-if="activeKey === sortKey && direction === 'asc'" :size="14" />
    <ArrowDown
      v-else-if="activeKey === sortKey && direction === 'desc'"
      :size="14"
    />
    <ArrowUpDown v-else :size="14" class="opacity-50" />
  </button>
</template>
