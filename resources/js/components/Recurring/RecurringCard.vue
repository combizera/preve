<script setup lang="ts">
import { ChartLine } from 'lucide-vue-next';
import { type Component } from 'vue';

import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface Props {
  title: string;
  description: string;
  icon?: Component;
  color?: 'positive' | 'destructive' | 'default';
}

withDefaults(defineProps<Props>(), {
  icon: ChartLine,
  color: 'default',
});
</script>

<template>
  <Card class="gap-2 bg-sidebar p-4">
    <div class="flex items-center gap-2 text-muted-foreground">
      <component :is="icon" class="size-5" />
      <p class="text-sm">
        {{ description }}
      </p>
    </div>
    <h2
      :class="
        cn(
          'text-lg font-medium',
          color === 'positive' && 'text-positive',
          color === 'destructive' && 'text-destructive',
        )
      "
    >
      {{ title }}
    </h2>
  </Card>
</template>
