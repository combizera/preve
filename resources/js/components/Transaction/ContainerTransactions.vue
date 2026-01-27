<script setup lang="ts">
import CardTransaction from '@/components/Transaction/CardTransaction.vue';
import { ITransaction } from '@/types/models/transaction';

defineProps<{
  transactions: ITransaction[];
}>();
</script>

<template>
  <div class="space-y-2">
    <CardTransaction
      :transaction="transaction"
      v-for="transaction in transactions"
      :key="transaction.id"
    />
  </div>
</template>
