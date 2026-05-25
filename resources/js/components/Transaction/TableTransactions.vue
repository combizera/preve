<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { Badge } from '@/components/ui/badge';
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

type SortKey = 'date' | 'description' | 'category' | 'amount';
type SortDirection = 'asc' | 'desc';

const sortKey = ref<SortKey | null>(null);
const sortDirection = ref<SortDirection>('asc');

const cycleSort = (key: SortKey) => {
  if (sortKey.value !== key) {
    sortKey.value = key;
    sortDirection.value = 'asc';
    return;
  }
  if (sortDirection.value === 'asc') {
    sortDirection.value = 'desc';
    return;
  }
  sortKey.value = null;
  sortDirection.value = 'asc';
};

const filteredRows = computed<ITransaction[]>(() => {
  if (!props.filters.type) {
    return props.transactions.filter(
      (tx) => tx.type === TRANSACTION_TYPE.EXPENSE,
    );
  }
  return props.transactions;
});

const compareBy: Record<SortKey, (a: ITransaction, b: ITransaction) => number> =
  {
    date: (a, b) =>
      (a.transaction_date ?? '').localeCompare(b.transaction_date ?? ''),
    description: (a, b) =>
      a.description.localeCompare(b.description, locale.value),
    category: (a, b) =>
      (a.category?.name ?? '').localeCompare(
        b.category?.name ?? '',
        locale.value,
      ),
    amount: (a, b) => a.amount - b.amount,
  };

const rows = computed<ITransaction[]>(() => {
  if (sortKey.value === null) return filteredRows.value;

  const dir = sortDirection.value === 'asc' ? 1 : -1;
  const compare = compareBy[sortKey.value];
  return [...filteredRows.value].sort((a, b) => compare(a, b) * dir);
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

const sortButtonClass =
  '-mx-2 inline-flex items-center gap-1 rounded px-2 py-1 transition-colors hover:bg-muted hover:text-foreground';
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead class="w-32">
          <button
            type="button"
            @click="cycleSort('date')"
            :class="sortButtonClass"
          >
            {{ t('models.transaction.date') }}
            <ArrowUp
              v-if="sortKey === 'date' && sortDirection === 'asc'"
              :size="14"
            />
            <ArrowDown
              v-else-if="sortKey === 'date' && sortDirection === 'desc'"
              :size="14"
            />
            <ArrowUpDown v-else :size="14" class="opacity-50" />
          </button>
        </TableHead>
        <TableHead>
          <button
            type="button"
            @click="cycleSort('description')"
            :class="sortButtonClass"
          >
            {{ t('models.transaction.description') }}
            <ArrowUp
              v-if="sortKey === 'description' && sortDirection === 'asc'"
              :size="14"
            />
            <ArrowDown
              v-else-if="sortKey === 'description' && sortDirection === 'desc'"
              :size="14"
            />
            <ArrowUpDown v-else :size="14" class="opacity-50" />
          </button>
        </TableHead>
        <TableHead>
          <button
            type="button"
            @click="cycleSort('category')"
            :class="sortButtonClass"
          >
            {{ t('models.category.name') }}
            <ArrowUp
              v-if="sortKey === 'category' && sortDirection === 'asc'"
              :size="14"
            />
            <ArrowDown
              v-else-if="sortKey === 'category' && sortDirection === 'desc'"
              :size="14"
            />
            <ArrowUpDown v-else :size="14" class="opacity-50" />
          </button>
        </TableHead>
        <TableHead>{{ t('tags.title') }}</TableHead>
        <TableHead class="text-right">
          <button
            type="button"
            @click="cycleSort('amount')"
            :class="cn(sortButtonClass, 'ml-auto')"
          >
            {{ t('models.transaction.amount') }}
            <ArrowUp
              v-if="sortKey === 'amount' && sortDirection === 'asc'"
              :size="14"
            />
            <ArrowDown
              v-else-if="sortKey === 'amount' && sortDirection === 'desc'"
              :size="14"
            />
            <ArrowUpDown v-else :size="14" class="opacity-50" />
          </button>
        </TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>
      <TableRow v-if="rows.length === 0">
        <TableCell colspan="5" class="text-center text-muted-foreground">
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
        <TableCell>
          <div v-if="tx.tags?.length" class="flex flex-wrap gap-1">
            <Badge
              v-for="tag in tx.tags"
              :key="tag.id"
              variant="secondary"
              class="font-normal"
            >
              {{ tag.name }}
            </Badge>
          </div>
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
        <TableCell colspan="4" class="text-right text-muted-foreground">
          {{ t('transactions.table.totalIncome') }}
        </TableCell>
        <TableCell class="text-right font-mono whitespace-nowrap text-positive">
          + {{ getCurrencySymbol() }} {{ formatCentsToDisplay(totals.income) }}
        </TableCell>
      </TableRow>
      <TableRow v-if="totals.expense > 0">
        <TableCell colspan="4" class="text-right text-muted-foreground">
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
