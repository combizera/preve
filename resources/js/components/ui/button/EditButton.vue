<script setup lang="ts">
import { Edit } from 'lucide-vue-next';
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
  <DropdownMenuItem @click="emit('click')">
    <Edit class="size-4" />
    {{ title ?? t('generic.actions.edit') }}
  </DropdownMenuItem>
</template>