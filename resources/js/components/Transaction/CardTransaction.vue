<script setup lang="ts">
import { ref } from 'vue';

import ActionGroup from '@/components/ActionGroup.vue';
import EditTransactionDialog from '@/components/Transaction/EditTransactionDialog.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { ITransaction } from '@/types/models/transaction';

defineProps<{
  transaction: ITransaction;
}>();

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedTransaction = ref<ITransaction | null>(null);

const openEditDialog = (transaction: ITransaction) => {
  selectedTransaction.value = transaction;
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
    <div class="flex items-center gap-2">
      <Checkbox id="transaction" />
      <div>
        <Label
          for="transaction"
          class="text-sm leading-6 font-medium text-muted-foreground"
        >
          {{ transaction.description }}
        </Label>
      </div>
    </div>

    <div>
      <span class="text-sm font-medium text-muted-foreground">
        {{ transaction.amount }}
      </span>
      <ActionGroup>
        <EditButton @click="openEditDialog(transaction)" />

        <DeleteButton @click="openDeleteDialog(transaction)" />
      </ActionGroup>
    </div>
  </Card>

  <EditTransactionDialog
    v-if="showEditDialog && selectedTransaction"
    v-model:open="showEditDialog"
    :transaction="selectedTransaction"
  />
</template>
