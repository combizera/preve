<script setup lang="ts">
import { ArrowDownLeft, ArrowUpRight, RefreshCw } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteTransactionDialog from '@/components/Transaction/DeleteTransactionDialog.vue';
import FormTransactionDialog from '@/components/Transaction/FormTransactionDialog.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import DuplicateButton from '@/components/ui/button/DuplicateButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import InfoButton from '@/components/ui/button/InfoButton.vue';
import ShareButton from '@/components/ui/button/ShareButton.vue';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { ITransaction } from '@/types/models/transaction';
import { isPastDate } from '@/utils/isPastDate';

const { t } = useI18n();

const props = defineProps<{
  transaction: ITransaction;
}>();

const formattedAmount = computed(() =>
  formatCentsToDisplay(props.transaction.amount),
);

const categoryIcon = computed(() =>
  getIconComponent(props.transaction.category?.icon ?? null),
);

const amountClass = computed(() =>
  cn(
    'text-sm font-medium',
    props.transaction.type === 'expense'
      ? "text-foreground/70 before:content-['-']"
      : "text-positive before:content-['+']",
  ),
);

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

const transactionIsPast = computed(() =>
  isPastDate(props.transaction.transaction_date),
);
const isExpense = computed(
  () => props.transaction.type === TRANSACTION_TYPE.EXPENSE,
);
</script>

<template>
  <Card
    :data-past="transactionIsPast"
    class="flex flex-row items-center justify-between gap-2 rounded-md bg-sidebar p-4 data-[past=true]:border-dashed"
  >
    <div class="flex items-center gap-4">
      <Checkbox
        :id="transaction.id"
        class="size-5 bg-background disabled:cursor-default disabled:opacity-80"
        v-model="transactionIsPast"
        :disabled="true"
      />
      <div class="space-y-1">
        <Label
          for="transaction"
          :class="
            cn(
              'text-sm leading-none font-medium text-foreground',
              transactionIsPast && 'text-muted-foreground line-through',
            )
          "
        >
          {{ transaction.description }}
        </Label>
        <span
          class="flex items-center gap-1 text-xs leading-none font-medium text-muted-foreground"
        >
          <span class="flex items-center gap-1">
            <component :is="categoryIcon" :size="14" />
            {{ transaction.category?.name }}
          </span>
          <span class="flex items-center gap-0.5">
            •
            <ArrowUpRight v-if="isExpense" :size="16" />
            <ArrowDownLeft v-else :size="16" />
            {{ capitalizeFirstLetter(transaction.type) }}
          </span>
          <span
            v-if="transaction.recurring_transaction_id"
            class="flex items-center gap-1"
          >
            • <RefreshCw :size="12" /> {{ t('transactions.recurring') }}
          </span>
        </span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <span :class="amountClass">
        {{ getCurrencySymbol() }} {{ formattedAmount }}
      </span>
      <ActionGroup>
        <InfoButton :transactionId="transaction.id" />

        <ShareButton :transactionId="transaction.id" />

        <DuplicateButton @click="openEditDialog(transaction, 'duplicate')" />

        <EditButton @click="openEditDialog(transaction, 'edit')" />

        <DeleteButton @click="openDeleteDialog(transaction)" />
      </ActionGroup>
    </div>
  </Card>

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
