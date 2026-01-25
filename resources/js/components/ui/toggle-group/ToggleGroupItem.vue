<script setup lang="ts">
import { computed, inject, type Ref } from 'vue';

import { cn } from '@/lib/utils';

interface Props {
  value: string | number;
  class?: string;
}

const props = defineProps<Props>();

const modelValue = inject<Ref<string | number | undefined>>('toggleGroupValue');
const updateValue = inject<(value: string | number) => void>('updateToggleGroupValue');

const isSelected = computed(() => modelValue?.value === props.value);

const handleClick = () => {
  if (updateValue) {
    updateValue(props.value);
  }
};
</script>

<template>
  <button
    type="button"
    :class="
      cn(
        'hover:cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
        isSelected
          ? 'bg-background text-foreground shadow-sm'
          : 'hover:bg-muted hover:text-foreground',
        props.class,
      )
    "
    @click="handleClick"
  >
    <slot />
  </button>
</template>
