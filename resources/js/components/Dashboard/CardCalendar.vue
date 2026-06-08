<script setup lang="ts">
import { Button } from '@/components/ui/button';
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
  <Button
    :variant="isSelected ? 'default' : 'ghost'"
    :class="
      cn(
        'group relative flex h-full flex-col gap-0 disabled:opacity-100',
        !isSelected && 'border border-transparent',
      )
    "
    @click="emit('select')"
    :disabled="isSelected"
  >
    <p class="font-medium">
      {{ month }}
    </p>
    <span
      :class="
        cn(
          'text-[12px] leading-none text-muted-foreground',
          isSelected && 'text-primary-foreground',
        )
      "
    >
      {{ year }}
    </span>
    <span
      v-if="isCurrent"
      :class="
        cn(
          'absolute top-1 size-1.5 rounded-full',
          isSelected ? 'bg-primary-foreground' : 'bg-primary',
        )
      "
    />
  </Button>
</template>
