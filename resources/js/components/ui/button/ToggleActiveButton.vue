<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pause, Play } from 'lucide-vue-next';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
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
  <DropdownMenuItem @click="toggleActive">
    <Pause v-if="isActive" :size="14" class="fill-foreground stroke-1" />
    <Play v-else :size="14" class="fill-foreground" />
    {{ isActive ? 'Pause' : 'Resume' }}
  </DropdownMenuItem>
</template>
