<script setup lang="ts">
import { provide, toRef } from 'vue';

import { cn } from '@/lib/utils';

interface Props {
  modelValue?: string | number;
  class?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  'update:modelValue': [value: string | number];
}>();

provide('toggleGroupValue', toRef(() => props.modelValue));
provide('updateToggleGroupValue', (value: string | number) => {
  emit('update:modelValue', value);
});
</script>

<template>
  <div
    :class="
      cn(
        'inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground',
        props.class,
      )
    "
    role="group"
  >
    <slot />
  </div>
</template>
