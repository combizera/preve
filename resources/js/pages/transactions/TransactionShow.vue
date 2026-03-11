<script setup lang="ts">
import { clsx } from 'clsx';
import { StickyNote } from 'lucide-vue-next';

import DetailItem from '@/components/Transaction/DetailItem.vue';
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
} from '@/components/ui/card';
import { formatCentsToDisplay } from '@/lib/currency';
import { ITransaction } from '@/types/models/transaction';
import { formatTransactionDate } from '@/utils/formatDate';

interface Props {
  transaction: ITransaction;
}

const props = defineProps<Props>();

function formattedAmount(transaction: ITransaction) {
  return formatCentsToDisplay(transaction.amount);
}

const transactionDetails = [
  { label: 'Date:', value: formatTransactionDate(props.transaction.transaction_date) },
  { label: 'Category:', value: props.transaction.category?.name },
  { label: 'Tag:', value: props.transaction.tag?.name },
  { label: 'Created At:', value: formatTransactionDate(props.transaction.created_at) },
];

</script>

<template>
  <div class="mx-auto max-w-lg py-14">
    <Card class="mx-auto min-w-lg gap-0">
      <CardHeader class="text-lg font-medium">
        {{ transaction.description }}
      </CardHeader>
      <CardContent class="-mb-4">
        <div class="my-4 space-y-4 rounded-lg bg-muted p-4">
          <ul class="space-y-2">
            <DetailItem
              v-for="detail in transactionDetails"
              :key="detail.label"
              :label="detail.label"
              :value="detail.value"
            />
          </ul>

          <div
            class="h-px w-full border-b border-muted-foreground/40"
          />

          <ul class="space-y-2">
            <DetailItem
              label="Amount:"
              :value="`R$ ${formattedAmount(transaction)}`"
              :className="clsx(
                'text-md text-lg font-medium',
                transaction.type === 'expense' && 'text-destructive before:content-[\'-\']',
                transaction.type === 'income' && 'text-positive'
              )"
            />
          </ul>
        </div>
      </CardContent>

      <CardFooter v-if="transaction.notes" class="mt-4 py-0">
        <div class="notes-block relative w-full overflow-hidden rounded-md bg-muted/50 px-4 py-3">
          <div class="mb-1 flex items-center gap-1.5 text-sm font-medium text-muted-foreground">
            <StickyNote :size="14" />
            Notes
          </div>
          <p class="text-sm text-foreground/80">
            {{ transaction.notes }}
          </p>
        </div>
      </CardFooter>
    </Card>
  </div>
</template>

<style scoped>
.notes-block::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  border-radius: 4px 0 0 4px;
  background: linear-gradient(to bottom, #facc15, #fef08a);
}
</style>
