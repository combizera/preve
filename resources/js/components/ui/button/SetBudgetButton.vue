<script setup lang="ts">
import { Wallet } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';

const { t } = useI18n();

interface Props {
  hasSeries: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  click: [];
}>();
</script>

<template>
  <DropdownMenuItem @click="emit('click')">
    <Wallet class="size-4" />
    {{ hasSeries ? t('categories.actions.manageBudget') : t('categories.actions.setBudget') }}
  </DropdownMenuItem>
</template>
