<script setup lang="ts">
import { ChevronDown, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
  defineProps<{
    title: string;
    collapsible?: boolean;
    defaultOpen?: boolean;
  }>(),
  {
    collapsible: false,
    defaultOpen: false,
  },
);

const open = ref(props.collapsible ? props.defaultOpen : true);

const toggle = () => {
  if (props.collapsible) {
    open.value = !open.value;
  }
};
</script>

<template>
  <div class="double-border flex flex-col bg-sidebar">
    <component
      :is="collapsible ? 'button' : 'div'"
      :type="collapsible ? 'button' : undefined"
      :class="
        cn(
          'flex items-center justify-between p-2 pb-3 text-left',
          collapsible && 'cursor-pointer',
        )
      "
      @click="toggle"
    >
      <span class="flex items-center gap-1 text-foreground">
        <Plus class="size-4" />
        <span class="text-sm">{{ title }}</span>
      </span>
      <ChevronDown
        v-if="collapsible"
        :class="
          cn(
            'size-4 text-muted-foreground transition-transform',
            open && 'rotate-180',
          )
        "
      />
    </component>

    <div v-show="open" class="rounded-lg border bg-background p-4">
      <slot />
    </div>
  </div>
</template>
