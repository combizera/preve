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

const groupedTransactions = computed(() => {
  const monthsTransactions: Record<string, ITransaction[]> = {};

  props.transactions.forEach((transaction) => {
    /**
    * Derives displayed month/year from transaction.transaction_date
    * Falls back to current date if no transaction.transaction_date is set
    */
    const [year, month] = (transaction.transaction_date ?? new Date().toISOString().slice(0, 10)).split('-');
    const label = new Date(+year, +month - 1).toLocaleDateString('en-US', { month: 'short' });
    const monthYear = `${label} - ${year}`;

    if (!monthsTransactions[monthYear]) {
      monthsTransactions[monthYear] = [];
    }

    monthsTransactions[monthYear].push(transaction);
  });

  return monthsTransactions;
});
</script>

<template>
  <div class="space-y-2">
    <!-- Empty State -->
    <EmptyState
      v-if="transactions.length === 0"
      title="No transactions yet"
      description="Start by creating your first transaction"
      button-text="Create Transaction"
      @action="emit('create')"
    />

    <section name="grouped-transactions" v-else>
      <div v-for="(group, monthYear) in groupedTransactions" :key="monthYear" class="space-y-2">
        <!-- TITLE -->
        <SectionTitle :title="monthYear" />

        <CardTransaction 
          :transaction="transaction"
          :key="transaction.id"
          v-for="transaction in group" 
         />
      </div>
    </section>
  </div>
</template>
