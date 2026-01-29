<script setup lang="ts">
import { computed } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import SectionTitle from '@/components/SectionTitle.vue';
import CardTransaction from '@/components/Transaction/CardTransaction.vue';
import { ITransaction } from '@/types/models/transaction';

defineProps<{
  transactions: ITransaction[];
}>();

const emit = defineEmits<{
  create: [];
}>();

/**
 * Formats current month and year as "Jan - 2026"
 */
const currentMonthYear = computed<string>(() => {
  const month = new Date().toLocaleDateString('en-US', { month: 'short' });
  const year = new Date().getFullYear();
  return `${month} - ${year}`;
});
</script>

<template>
  <div class="space-y-2">
    <!-- TITLE -->
    <SectionTitle :title="currentMonthYear" />

    <!-- Empty State -->
    <EmptyState
      v-if="transactions.length === 0"
      title="No transactions yet"
      description="Start by creating your first transaction"
      button-text="Create Transaction"
      @action="emit('create')"
    />

    <CardTransaction
      :transaction="transaction"
      :key="transaction.id"
      v-for="transaction in transactions"
      v-else
    />
  </div>
</template>
