<script setup lang="ts">
import { computed } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import SectionTitle from '@/components/SectionTitle.vue';
import CardTransaction from '@/components/Transaction/CardTransaction.vue';
import { type ITransactionFilters } from '@/types/filters';
import { type ITransaction } from '@/types/models/transaction';

const props = defineProps<{
  transactions: ITransaction[];
  filters: ITransactionFilters;
}>();

const emit = defineEmits<{
  create: [];
}>();

/**
 * Derives displayed month/year from filters.date_start
 * Falls back to current date if no filter is set
 */
const currentMonthYear = computed<string>(() => {
  const [year, month] = (props.filters.date_start ?? new Date().toISOString().slice(0, 10)).split('-');
  const label = new Date(+year, +month - 1).toLocaleDateString('en-US', { month: 'short' });
  return `${label} - ${year}`;
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
