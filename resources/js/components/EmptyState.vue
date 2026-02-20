<script setup lang="ts">
import { Plus } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

interface Props {
  title: string;
  description: string;
  buttonText?: string;
  showButton?: boolean;
}

withDefaults(defineProps<Props>(), {
  showButton: true,
});

const emit = defineEmits<{
  action: [];
}>();
</script>

<template>
  <Card
    class="flex flex-col items-center justify-center gap-4 bg-sidebar p-8 text-center"
  >
    <div class="flex flex-col gap-1 items-center">
      <slot name="icon" />
      <h3 class="text-md font-semibold text-foreground">
        {{ title }}
      </h3>
      <p class="text-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>
    <Button
      v-if="showButton"
      size="sm"
      variant="default"
      @click="emit('action')"
    >
      <Plus :size="16" />
      {{ buttonText }}
    </Button>
  </Card>
</template>
