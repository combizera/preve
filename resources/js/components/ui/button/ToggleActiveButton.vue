<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pause, Play } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { toggle } from '@/routes/recurring';

interface Props {
  id: string | number;
  isActive: boolean;
}

const props = defineProps<Props>();

const toggleActive = () => {
  router.patch(toggle(Number(props.id)).url);
};
</script>

<template>
  <Button variant="ghost" size="sm" @click="toggleActive" class="gap-1.5">
    <Pause v-if="isActive" :size="14" />
    <Play v-else :size="14" />
    <span class="text-xs">
      {{ isActive ? 'Pause' : 'Resume' }}
    </span>
  </Button>
</template>
