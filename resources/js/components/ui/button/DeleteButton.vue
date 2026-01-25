<script setup lang="ts">
import { Trash } from 'lucide-vue-next';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';

defineProps<{
  title?: string;
}>();

const emit = defineEmits<{
  click: [];
}>();
</script>

<template>
  <DropdownMenuItem variant="destructive" @click="emit('click')">
    <Trash class="size-4" />
    {{ title ?? 'Delete' }}
  </DropdownMenuItem>
</template>