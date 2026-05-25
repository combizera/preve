<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import {
  Table,
  TableBody,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { cn } from '@/lib/utils';
import type { ITransactionFilters } from '@/types/filters';
import type { ITransaction } from '@/types/models/transaction';

const props = defineProps<{
  transactions: ITransaction[];
  filters: ITransactionFilters;
}>();

const { t, locale } = useI18n();

const rows = computed<ITransaction[]>(() => {
  if (!props.filters.type) {
    return props.transactions.filter(
      (tx) => tx.type === TRANSACTION_TYPE.EXPENSE,
    );
  }
  return props.transactions;
});

const totals = computed(() => {
  let income = 0;
  let expense = 0;
  for (const tx of rows.value) {
    if (tx.type === TRANSACTION_TYPE.INCOME) {
      income += tx.amount;
    } else {
      expense += tx.amount;
    }
  }
  return { income, expense };
});

const formatDate = (value?: string | null): string => {
  if (!value) return '';
  return new Date(value).toLocaleDateString(locale.value, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead class="w-32">{{ t('models.transaction.date') }}</TableHead>
        <TableHead>{{ t('models.transaction.description') }}</TableHead>
        <TableHead>{{ t('models.category.name') }}</TableHead>
        <TableHead class="text-right">
          {{ t('models.transaction.amount') }}
        </TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>
      <TableRow v-if="rows.length === 0">
        <TableCell colspan="4" class="text-center text-muted-foreground">
          {{ t('transactions.table.empty') }}
        </TableCell>
      </TableRow>
      <TableRow v-for="tx in rows" :key="tx.id">
        <TableCell class="whitespace-nowrap text-muted-foreground">
          {{ formatDate(tx.transaction_date) }}
        </TableCell>
        <TableCell class="font-medium">{{ tx.description }}</TableCell>
        <TableCell>
          <span
            v-if="tx.category"
            class="flex items-center gap-1.5 text-muted-foreground"
          >
            <component
              :is="getIconComponent(tx.category.icon ?? null)"
              :size="14"
            />
            {{ tx.category.name }}
          </span>
        </TableCell>
        <TableCell
          :class="
            cn(
              'text-right font-mono whitespace-nowrap',
              tx.type === TRANSACTION_TYPE.INCOME
                ? 'text-positive'
                : 'text-foreground/80',
            )
          "
        >
          <span>{{ tx.type === TRANSACTION_TYPE.INCOME ? '+' : '−' }}</span>
          {{ getCurrencySymbol() }} {{ formatCentsToDisplay(tx.amount) }}
        </TableCell>
      </TableRow>
    </TableBody>

    <TableFooter v-if="rows.length > 0">
      <TableRow v-if="totals.income > 0">
        <TableCell colspan="3" class="text-right text-muted-foreground">
          {{ t('transactions.table.totalIncome') }}
        </TableCell>
        <TableCell class="text-right font-mono whitespace-nowrap text-positive">
          + {{ getCurrencySymbol() }} {{ formatCentsToDisplay(totals.income) }}
        </TableCell>
      </TableRow>
      <TableRow v-if="totals.expense > 0">
        <TableCell colspan="3" class="text-right text-muted-foreground">
          {{ t('transactions.table.totalExpense') }}
        </TableCell>
        <TableCell
          class="text-right font-mono whitespace-nowrap text-foreground/80"
        >
          − {{ getCurrencySymbol() }} {{ formatCentsToDisplay(totals.expense) }}
        </TableCell>
      </TableRow>
    </TableFooter>
  </Table>
</template>
