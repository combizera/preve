<script setup lang="ts">
import { PlusIcon } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { useTransactionStore } from '@/stores/transaction.store';

const transactionStore = useTransactionStore()
</script>

<template>
  <Button @click="transactionStore.openCreateDialog">
    <PlusIcon />
    Create
  </Button>
</template>
