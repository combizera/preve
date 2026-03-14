<script setup lang="ts">
import { computed, ref } from 'vue';
import { Repeat } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteTransactionDialog from '@/components/Transaction/DeleteTransactionDialog.vue';
import FormTransactionDialog from '@/components/Transaction/FormTransactionDialog.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import DuplicateButton from '@/components/ui/button/DuplicateButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import InfoButton from '@/components/ui/button/InfoButton.vue';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay } from '@/lib/currency';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { ITransaction } from '@/types/models/transaction';

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
      : 'text-positive',
  ),
);

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedTransaction = ref<ITransaction | null>(null);
const editMode = ref<'edit' | 'duplicate'>('edit');

const openEditDialog = (transaction: ITransaction, mode: 'edit' | 'duplicate' = 'edit') => {
  selectedTransaction.value = transaction;
  editMode.value = mode;
  showEditDialog.value = true;
};

const openDeleteDialog = (transaction: ITransaction) => {
  selectedTransaction.value = transaction;
  showDeleteDialog.value = true;
};
</script>

<template>
  <Card
    class="flex flex-row items-center justify-between gap-2 rounded-md bg-sidebar p-4"
  >
    <div class="flex items-center gap-4">
      <Checkbox id="transaction" class="size-5 bg-background cursor-pointer" />
      <div class="space-y-1">
        <Label
          for="transaction"
          class="text-sm leading-none font-medium text-foreground"
        >
          {{ transaction.description }}
        </Label>
        <span
          class="flex items-center gap-1 text-xs leading-none font-medium text-muted-foreground"
        >
          <component :is="categoryIcon" :size="14" />
          {{ transaction.category?.name }} •
          {{ capitalizeFirstLetter(transaction.type) }}
          <span v-if="transaction.recurring_transaction_id" class="flex items-center gap-1">
            • <Repeat :size="12" /> {{ t('transactions.recurring') }}
          </span>
        </span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <span :class="amountClass"> R$ {{ formattedAmount }} </span>
      <ActionGroup>
        <InfoButton :transactionId="transaction.id" />

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
