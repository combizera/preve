<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, inject, ref, watch } from 'vue';

import FormTransaction from '@/components/Transaction/FormTransaction.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import { extractNumbers, formatCentsToDisplay, parseToCents } from '@/lib/currency';
import { update } from '@/routes/transactions';
import type { ICategory } from '@/types/models/category';
import type { ITag } from '@/types/models/tag';
import { ITransaction } from '@/types/models/transaction';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  transaction: ITransaction;
}>();

const categories = inject<ICategory[]>('categories', []);
const tags = inject<ITag[]>('tags', []);

const rawAmount = ref('');

const form = useForm<ITransaction>({
  category_id: 0,
  tag_id: null,
  amount: 0,
  type: TRANSACTION_TYPE.EXPENSE,
  description: '',
  notes: '',
  transaction_date: '',
});

watch(
  () => open.value,
  (isOpen) => {
    if (isOpen) {
      rawAmount.value = props.transaction.amount.toString();
      form.category_id = props.transaction.category_id;
      form.tag_id = props.transaction.tag_id;
      form.amount = props.transaction.amount;
      form.type = props.transaction.type;
      form.description = props.transaction.description;
      form.notes = props.transaction.notes ?? '';
      form.transaction_date = props.transaction.transaction_date.split('T')[0];
    }
  },
  { immediate: true }
);

const displayAmount = computed({
  get: () => formatCentsToDisplay(rawAmount.value),
  set: (value: string) => {
    const numbers = extractNumbers(value);
    rawAmount.value = numbers;
    form.amount = parseToCents(numbers);
  },
});

const updateTransaction = () => {
  form.submit(update(props.transaction.id), {
    onSuccess: () => {
      open.value = false;
      form.reset();
      rawAmount.value = '';
    },
  });
};
</script>

<template>
  <Dialog v-model:open="open">
    <form>
      <DialogContent class="sm:max-w-[550px]">
        <DialogHeader>
          <DialogTitle>Update Transaction</DialogTitle>
          <DialogDescription>
            Fill in the details below to update a transaction.
          </DialogDescription>
        </DialogHeader>

        <FormTransaction
          :form="form"
          :displayAmount="displayAmount"
          :categories="categories"
          :tags="tags"
        />

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline"> Cancel </Button>
          </DialogClose>
          <Button
            type="button"
            @click="updateTransaction"
            :disabled="form.processing"
          >
            Edit Transaction
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
