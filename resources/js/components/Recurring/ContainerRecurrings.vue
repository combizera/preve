<script setup lang="ts">
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

defineProps<Props>();
</script>

<template>
  <div class="space-y-3">
    <SectionTitle :title="type === 'income' ? 'Income' : 'Expense'" />

    <div class="space-y-2">
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
