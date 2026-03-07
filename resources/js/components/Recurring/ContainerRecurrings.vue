<script setup lang="ts">
import { capitalize, computed } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import CardRecurring from '@/components/Recurring/CardRecurring.vue';
import SectionTitle from '@/components/SectionTitle.vue';
import { useRecurringStore } from '@/stores/recurring.store';
import { type ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';
import { TransactionType } from '@/types/models/transaction';

interface Props {
  recurringTransactions: IRecurringTransaction[];
  categories: ICategory[];
  tags: ITag[];
  type: TransactionType;
}

const recurringStore = useRecurringStore();
const props = defineProps<Props>();

const emptyStateConfig = computed(() => ({
  title: `No ${props.type} transactions yet`,
  description: `Start by creating your first recurring ${props.type}`,
  buttonText: `Create ${capitalize(props.type)}`,
}));
</script>

<template>
  <div class="space-y-3">
    <!-- TITLE -->
    <SectionTitle :title="type === 'income' ? 'Income' : 'Expense'" />

    <!-- Empty State -->
    <EmptyState
      v-if="recurringTransactions.length === 0"
      :title="emptyStateConfig.title"
      :description="emptyStateConfig.description"
      :button-text="emptyStateConfig.buttonText"
      @action="recurringStore.openCreateDialog(type)"
    />

    <!-- Transactions List -->
    <div v-else class="space-y-2">
      <CardRecurring
        :recurringTransaction="recurringTransaction"
        :categories="categories"
        :tags="tags"
        v-for="recurringTransaction in recurringTransactions"
        :key="recurringTransaction.id"
      />
    </div>
  </div>
</template>
