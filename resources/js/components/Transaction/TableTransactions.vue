<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteTransactionDialog from '@/components/Transaction/DeleteTransactionDialog.vue';
import FormTransactionDialog from '@/components/Transaction/FormTransactionDialog.vue';
import SortableHeader from '@/components/Transaction/SortableHeader.vue';
import TableBulkActions from '@/components/Transaction/TableBulkActions.vue';
import { Badge } from '@/components/ui/badge';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import DuplicateButton from '@/components/ui/button/DuplicateButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import InfoButton from '@/components/ui/button/InfoButton.vue';
import ShareButton from '@/components/ui/button/ShareButton.vue';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Table,
  TableBody,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useRowSelection } from '@/composables/useRowSelection';
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

const { selectedIds, headerState, toggleAll, toggleRow, isSelected, clear } =
  useRowSelection(rows, (tx) => tx.id);

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedTransaction = ref<ITransaction | null>(null);
const editMode = ref<'edit' | 'duplicate'>('edit');

const openEditDialog = (
  transaction: ITransaction,
  mode: 'edit' | 'duplicate' = 'edit',
) => {
  selectedTransaction.value = transaction;
  editMode.value = mode;
  showEditDialog.value = true;
};

const openDeleteDialog = (transaction: ITransaction) => {
  selectedTransaction.value = transaction;
  showDeleteDialog.value = true;
};

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
  <TableBulkActions
    v-if="selectedIds.length > 0"
    :selected-ids="selectedIds"
    @cleared="clear"
  />

  <Table>
    <TableHeader>
      <TableRow>
        <TableHead class="w-10">
          <Checkbox
            :model-value="headerState"
            @update:model-value="toggleAll"
          />
        </TableHead>
        <TableHead class="w-32">
          <SortableHeader
            sort-key="date"
            :active-key="sortKey"
            :direction="sortDirection"
            @sort="cycleSort"
          >
            {{ t('models.transaction.date') }}
          </SortableHeader>
        </TableHead>
        <TableHead>
          <SortableHeader
            sort-key="description"
            :active-key="sortKey"
            :direction="sortDirection"
            @sort="cycleSort"
          >
            {{ t('models.transaction.description') }}
          </SortableHeader>
        </TableHead>
        <TableHead>
          <SortableHeader
            sort-key="category"
            :active-key="sortKey"
            :direction="sortDirection"
            @sort="cycleSort"
          >
            {{ t('models.category.name') }}
          </SortableHeader>
        </TableHead>
        <TableHead>{{ t('tags.title') }}</TableHead>
        <TableHead class="text-right">
          <SortableHeader
            sort-key="amount"
            :active-key="sortKey"
            :direction="sortDirection"
            align="right"
            @sort="cycleSort"
          >
            {{ t('models.transaction.amount') }}
          </SortableHeader>
        </TableHead>
        <TableHead class="text-right">
          {{ t('generic.labels.actions') }}
        </TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>
      <TableRow v-if="rows.length === 0">
        <TableCell colspan="7" class="text-center text-muted-foreground">
          {{ t('transactions.table.empty') }}
        </TableCell>
      </TableRow>
      <TableRow
        v-for="tx in rows"
        :key="tx.id"
        :data-state="tx.id && isSelected(tx.id) ? 'selected' : undefined"
      >
        <TableCell class="w-10">
          <Checkbox
            v-if="tx.id"
            :model-value="isSelected(tx.id)"
            @update:model-value="(checked) => toggleRow(tx.id!, checked)"
          />
        </TableCell>
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
        <TableCell class="text-right">
          <ActionGroup>
            <InfoButton :transactionId="tx.id" />
            <ShareButton :transactionId="tx.id" />
            <DuplicateButton @click="openEditDialog(tx, 'duplicate')" />
            <EditButton @click="openEditDialog(tx, 'edit')" />
            <DeleteButton @click="openDeleteDialog(tx)" />
          </ActionGroup>
        </TableCell>
      </TableRow>
    </TableBody>

    <TableFooter v-if="rows.length > 0">
      <TableRow v-if="totals.income > 0">
        <TableCell colspan="5" />
        <TableCell class="text-right text-muted-foreground">
          {{ t('transactions.table.totalIncome') }}
        </TableCell>
        <TableCell class="text-right font-mono whitespace-nowrap text-positive">
          + {{ getCurrencySymbol() }} {{ formatCentsToDisplay(totals.income) }}
        </TableCell>
      </TableRow>
      <TableRow v-if="totals.expense > 0">
        <TableCell colspan="5" />
        <TableCell class="text-right text-muted-foreground">
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

  <FormTransactionDialog
    v-if="showEditDialog && selectedTransaction"
    v-model:open="showEditDialog"
    :transaction="selectedTransaction"
    :type="editMode"
  />

  <DeleteTransactionDialog
    v-if="showDeleteDialog && selectedTransaction"
    v-model:open="showDeleteDialog"
    :transaction="selectedTransaction"
  />
</template>
