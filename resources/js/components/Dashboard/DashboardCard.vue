<script setup lang="ts">

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  title: string;
  description: string;
  amount: string;
}

defineProps<Props>();
</script>

<template>
  <Card class="gap-0">
    <CardHeader>
      <CardTitle class="text-sm font-medium text-muted-foreground">
        {{ title }}
      </CardTitle>
    </CardHeader>
    <CardContent>
      <p
        class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400"
      >
        R$ {{ amount }}
      </p>
      <p class="text-xs text-muted-foreground mt-1">
        {{ description }}
      </p>
    </CardContent>
  </Card>
</template>
