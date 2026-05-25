<script setup lang="ts">
import { LayoutList, Table as TableIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useTransactionView } from '@/composables/useTransactionView';

const { t } = useI18n();
const { view } = useTransactionView();
</script>

<template>
  <div class="rounded-md-outer border p-1">
    <ToggleGroup
      :model-value="view"
      @update:model-value="(value) => (view = value as 'list' | 'table')"
      class="h-9"
    >
      <ToggleGroupItem value="list" class="gap-1.5 px-3">
        <LayoutList :size="16" />
        <span class="text-sm">{{ t('transactions.view.list') }}</span>
      </ToggleGroupItem>
      <ToggleGroupItem value="table" class="gap-1.5 px-3">
        <TableIcon :size="16" />
        <span class="text-sm">{{ t('transactions.view.table') }}</span>
      </ToggleGroupItem>
    </ToggleGroup>
  </div>
</template>
