<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
  label: string;
  value?: string | null;
  className?: string;
}>();
</script>

<template>
  <li
    v-if="value"
    class="flex justify-between gap-2 text-sm text-muted-foreground"
  >
    <span>
      {{ label }}
    </span>
    <p :class="cn('max-w-xs text-right text-foreground', className)">
      {{ value }}
    </p>
  </li>
</template>
