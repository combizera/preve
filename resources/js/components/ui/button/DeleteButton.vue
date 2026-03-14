<script setup lang="ts">
import { Trash } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';

const { t } = useI18n();

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
    {{ title ?? t('generic.actions.delete') }}
  </DropdownMenuItem>
</template>