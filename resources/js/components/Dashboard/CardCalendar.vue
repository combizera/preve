<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
  month: string;
  year: number;
  isSelected?: boolean;
  isCurrent?: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  select: [];
}>();
</script>

<template>
  <button
    type="button"
    :disabled="isSelected"
    @click="emit('select')"
    :class="cn(
      'relative flex flex-col items-center h-full min-w-25 rounded transition-all duration-150',
      isSelected
        ? 'from-primary to-primary/85 text-primary-foreground border border-zinc-950/25 bg-gradient-to-t shadow-sm shadow-zinc-950/20 cursor-default active:brightness-90 dark:border-white/20'
        : 'cursor-pointer hover:bg-muted-foreground/10 border border-transparent hover:shadow-sm hover:shadow-zinc-950/20 transition-[filter] duration-200 hover:brightness-110',
    )"
  >
    <p class="pt-2 text-md font-medium">
      {{ month }}
    </p>
    <span :class="cn('text-[10px] leading-[1]', isSelected ? 'text-primary-foreground/70' : 'text-muted-foreground')">
      {{ year }}
    </span>
    <span
      v-if="isCurrent"
      :class="cn(
        'absolute bottom-1 size-1.5 rounded-[2px] transition-colors',
        isSelected ? 'bg-primary-foreground' : 'bg-primary'
      )"
    />
  </button>
</template>
