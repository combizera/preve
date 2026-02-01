<script setup lang="ts">
import { ArrowDownLeft, ArrowUpRight, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

import RecurringCard from '@/components/Recurring/RecurringCard.vue';
import { formatCentsToDisplay } from '@/lib/currency';
import { calculateTotalMonthly } from '@/lib/recurring';
import type { IRecurringTransaction } from '@/types/models/recurring-transaction';

interface Props {
  incomeRecurring: IRecurringTransaction[];
  expenseRecurring: IRecurringTransaction[];
}

const props = defineProps<Props>();

/**
 * Check if there are any active recurring transactions
 */
const hasActiveTransactions = computed<boolean>(() => {
  const hasActiveIncome = props.incomeRecurring.some((t) => t.is_active);
  const hasActiveExpense = props.expenseRecurring.some((t) => t.is_active);
  return hasActiveIncome || hasActiveExpense;
});

/**
 * Total monthly income from all recurring income transactions
 */
const totalIncome = computed<string>(() => {
  const total = calculateTotalMonthly(props.incomeRecurring);
  return formatCentsToDisplay(total);
});

/**
 * Total monthly expense from all recurring expense transactions
 */
const totalExpense = computed<string>(() => {
  const total = calculateTotalMonthly(props.expenseRecurring);
  return formatCentsToDisplay(total);
});

/**
 * Monthly balance value in cents (income - expense)
 */
const balanceValue = computed<number>(() => {
  const incomeTotal = calculateTotalMonthly(props.incomeRecurring);
  const expenseTotal = calculateTotalMonthly(props.expenseRecurring);
  return incomeTotal - expenseTotal;
});

/**
 * Formatted balance for display
 */
const balance = computed<string>(() => formatCentsToDisplay(balanceValue.value));

/**
 * Color based on balance: positive (green), negative (red), or default
 */
const balanceColor = computed<'positive' | 'destructive' | 'default'>(() => {
  if (balanceValue.value > 0) return 'positive';
  if (balanceValue.value < 0) return 'destructive';
  return 'default';
});
</script>

<template>
  <section
    v-if="hasActiveTransactions"
    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
  >
    <RecurringCard
      :title="`R$ ${totalIncome}`"
      description="Monthly Income"
      :icon="ArrowDownLeft"
    />

    <RecurringCard
      :title="`R$ ${totalExpense}`"
      description="Monthly Expense"
      :icon="ArrowUpRight"
    />

    <RecurringCard
      :title="`R$ ${balance}`"
      description="Monthly Balance"
      :icon="TrendingUp"
      :color="balanceColor"
    />
  </section>
</template>
