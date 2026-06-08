<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
  ArrowDown,
  ArrowUp,
  ArrowUpDown,
  CreditCard,
  Trash,
} from 'lucide-vue-next';
import { computed, inject, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import {
  bulkAssignCreditCard,
  bulkDestroy,
} from '@/actions/App/Http/Controllers/TransactionController';
import ActionGroup from '@/components/ActionGroup.vue';
import DeleteTransactionDialog from '@/components/Transaction/DeleteTransactionDialog.vue';
import FormTransactionDialog from '@/components/Transaction/FormTransactionDialog.vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import DuplicateButton from '@/components/ui/button/DuplicateButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import InfoButton from '@/components/ui/button/InfoButton.vue';
import ShareButton from '@/components/ui/button/ShareButton.vue';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
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
import type { ICreditCard } from '@/types/models/credit-card';
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

const sortButtonClass =
  '-mx-2 inline-flex items-center gap-1 rounded px-2 py-1 transition-colors hover:bg-muted hover:text-foreground';

const selectedIds = ref<string[]>([]);

const selectableIds = computed(() =>
  rows.value.map((tx) => tx.id).filter((id): id is string => Boolean(id)),
);

watch(rows, () => {
  const visible = new Set(selectableIds.value);
  selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
});

const allSelected = computed(
  () =>
    selectableIds.value.length > 0 &&
    selectableIds.value.every((id) => selectedIds.value.includes(id)),
);

const headerState = computed<boolean | 'indeterminate'>(() => {
  if (allSelected.value) return true;
  return selectedIds.value.length > 0 ? 'indeterminate' : false;
});

const toggleAll = (checked: boolean | 'indeterminate') => {
  selectedIds.value = checked === true ? [...selectableIds.value] : [];
};

const toggleRow = (id: string, checked: boolean | 'indeterminate') => {
  if (checked === true) {
    if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
  } else {
    selectedIds.value = selectedIds.value.filter((value) => value !== id);
  }
};

const showBulkDeleteDialog = ref(false);
const bulkForm = useForm<{ ids: string[] }>({ ids: [] });

const confirmBulkDelete = () => {
  bulkForm.ids = [...selectedIds.value];

  bulkForm.delete(bulkDestroy().url, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      showBulkDeleteDialog.value = false;
    },
  });
};

const creditCards = inject<ICreditCard[]>('creditCards', []);
const cardSelectValue = ref<number | null>(null);
const cardAssignForm = useForm<{
  ids: string[];
  credit_card_id: number | null;
}>({ ids: [], credit_card_id: null });

const assignToCard = (cardId: number) => {
  cardAssignForm.ids = [...selectedIds.value];
  cardAssignForm.credit_card_id = cardId;

  cardAssignForm.patch(bulkAssignCreditCard().url, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      cardSelectValue.value = null;
    },
  });
};

const onCardSelect = (value: unknown) => {
  if (typeof value === 'number') assignToCard(value);
};
</script>

<template>
  <div
    v-if="selectedIds.length > 0"
    class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-md border bg-muted/40 px-3 py-2"
  >
    <span class="text-sm text-muted-foreground">
      {{ t('transactions.bulk.selected', { count: selectedIds.length }) }}
    </span>
    <div class="flex items-center gap-2">
      <Select
        v-if="creditCards.length > 0"
        :model-value="cardSelectValue ?? undefined"
        @update:model-value="onCardSelect"
      >
        <SelectTrigger size="sm" class="h-8 w-48">
          <CreditCard :size="14" class="text-muted-foreground" />
          <SelectValue :placeholder="t('transactions.bulk.assignCard')" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectItem
              v-for="card in creditCards"
              :key="card.id"
              :value="card.id"
            >
              {{ card.name }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
      <Button
        variant="destructive"
        size="sm"
        @click="showBulkDeleteDialog = true"
      >
        <Trash :size="14" />
        {{ t('transactions.bulk.delete') }}
      </Button>
    </div>
  </div>

  <Table>
    <TableHeader>
      <TableRow>
        <TableHead class="w-10">
          <Checkbox
            :model-value="headerState"
            :aria-label="t('transactions.bulk.delete')"
            @update:model-value="toggleAll"
          />
        </TableHead>
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
        :data-state="
          tx.id && selectedIds.includes(tx.id) ? 'selected' : undefined
        "
      >
        <TableCell class="w-10">
          <Checkbox
            v-if="tx.id"
            :model-value="selectedIds.includes(tx.id)"
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

  <AlertDialog v-model:open="showBulkDeleteDialog">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ t('transactions.bulk.confirm', { count: selectedIds.length }) }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>
          {{ t('generic.actions.cancel') }}
        </AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          :disabled="bulkForm.processing"
          @click="confirmBulkDelete"
        >
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
