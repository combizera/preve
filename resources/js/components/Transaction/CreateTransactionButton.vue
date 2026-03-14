<script setup lang="ts">
import { PlusIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { Button } from '@/components/ui/button';
import { useTransactionStore } from '@/stores/transaction.store';

const { t } = useI18n();
const transactionStore = useTransactionStore()
</script>

<template>
  <Button @click="transactionStore.openCreateDialog">
    <PlusIcon />
    {{ t('generic.actions.create') }}
  </Button>
</template>
