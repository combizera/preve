<script setup lang="ts">
import { Edit } from 'lucide-vue-next';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';

defineProps<{
  title?: string;
}>();

const emit = defineEmits<{
  click: [];
}>();
</script>

<template>
  <DropdownMenuItem @click="emit('click')">
    <Edit class="size-4" />
    {{ title ?? 'Edit' }}
  </DropdownMenuItem>
</template>