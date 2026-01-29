<script setup lang="ts">
import { computed } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import CardRecurring from '@/components/Recurring/CardRecurring.vue';
import SectionTitle from '@/components/SectionTitle.vue';
import { type ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';

interface Props {
  recurringTransactions: IRecurringTransaction[];
  categories: ICategory[];
  tags: ITag[];
  type?: 'income' | 'expense';
}

const props = defineProps<Props>();

const emit = defineEmits<{
  create: [];
}>();

const emptyStateConfig = computed(() => ({
  title: `No ${props.type === 'income' ? 'income' : 'expense'} transactions yet`,
  description: `Start by creating your first recurring ${props.type === 'income' ? 'income' : 'expense'}`,
  buttonText: `Create ${props.type === 'income' ? 'Income' : 'Expense'}`,
}));
</script>

<template>
  <div class="space-y-3">
    <SectionTitle :title="type === 'income' ? 'Income' : 'Expense'" />

    <!-- Empty State -->
    <EmptyState
      v-if="recurringTransactions.length === 0"
      :title="emptyStateConfig.title"
      :description="emptyStateConfig.description"
      :button-text="emptyStateConfig.buttonText"
      @action="emit('create')"
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
