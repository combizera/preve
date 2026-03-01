<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Variant = 'positive' | 'neutral' | 'destructive';

interface Props {
  title: string;
  description: string;
  amount: string;
  variant?: Variant;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'neutral',
});

const amountClass = computed(() => {
  const classes: Record<Variant, string> = {
    positive: 'text-positive',
    neutral: 'text-foreground',
    destructive: 'text-destructive',
  };

  return classes[props.variant];
});
</script>

<template>
  <Card class="gap-0">
    <CardHeader>
      <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
        <slot name="icon" />
        {{ title }}
      </CardTitle>
    </CardHeader>
    <CardContent>
      <p class="text-2xl font-bold font-mono" :class="amountClass">
        R$ {{ amount }}
      </p>
      <p class="text-xs text-muted-foreground mt-1">
        {{ description }}
      </p>
    </CardContent>
  </Card>
</template>
