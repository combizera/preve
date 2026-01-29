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
    class="flex flex-col items-center justify-center gap-3 bg-sidebar p-8 text-center"
  >
    <div class="space-y-1">
      <h3 class="text-md font-semibold text-muted-foreground">
        {{ title }}
      </h3>
      <p class="text-sm text-muted-foreground/70">
        {{ description }}
      </p>
    </div>
    <Button
      v-if="showButton"
      size="sm"
      variant="outline"
      @click="emit('action')"
    >
      <Plus :size="16" />
      {{ buttonText }}
    </Button>
  </Card>
</template>
