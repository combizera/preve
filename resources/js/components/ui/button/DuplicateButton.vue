<script setup lang="ts">
import { Copy } from 'lucide-vue-next';
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
    <Copy class="size-4" />
    {{ title ?? t('generic.actions.duplicate') }}
  </DropdownMenuItem>
</template>
