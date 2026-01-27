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
import {
  extractNumbers,
  formatCentsToDisplay,
  parseToCents,
} from '@/lib/currency';
import { update, store } from '@/routes/transactions';
import type { ICategory } from '@/types/models/category';
import type { ITag } from '@/types/models/tag';
import { ITransaction } from '@/types/models/transaction';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  transaction: ITransaction;
  type: 'edit' | 'duplicate';
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
  [() => open.value, () => props.transaction],
  ([isOpen, transaction]) => {
    if (isOpen && transaction) {
      const amount = transaction.amount ?? 0;
      // Initialize rawAmount with the transaction amount
      rawAmount.value = amount > 0 ? amount.toString() : '';
      form.category_id = transaction.category_id ?? 0;
      form.tag_id = transaction.tag_id ?? null;
      form.amount = amount;
      form.type = transaction.type ?? TRANSACTION_TYPE.EXPENSE;
      form.description = transaction.description ?? '';
      form.notes = transaction.notes ?? '';
      form.transaction_date = transaction.transaction_date
        ? transaction.transaction_date.split('T')[0]
        : new Date().toISOString().split('T')[0];
    }
  },
  { immediate: true },
);

const displayAmount = computed({
  get: () => formatCentsToDisplay(rawAmount.value),
  set: (value: string) => {
    const numbers = extractNumbers(value);
    rawAmount.value = numbers;
    form.amount = parseToCents(numbers);
  },
});

const createTransaction = () => {
  form.submit(store(), {
    onSuccess: () => {
      open.value = false;
      form.reset();
      rawAmount.value = '';
    },
  });
};

const updateTransaction = () => {
  form.submit(update(props.transaction.id), {
    onSuccess: () => {
      open.value = false;
      form.reset();
      rawAmount.value = '';
    },
  });
};

const handleSubmit = () => {
  if (props.type === 'duplicate') {
    createTransaction();
  } else {
    updateTransaction();
  }
};

const submitButtonText = computed(() => {
  return props.type === 'duplicate' ? 'Duplicate Transaction' : 'Edit Transaction';
});
</script>

<template>
  <Dialog v-model:open="open">
    <form>
      <DialogContent class="sm:max-w-[550px]">
        <DialogHeader>
          <DialogTitle>
            {{ type === 'duplicate' ? 'Duplicate' : 'Edit' }} Transaction
          </DialogTitle>
          <DialogDescription>
            Fill in the details below to
            {{ type === 'duplicate' ? 'Duplicate' : 'Edit' }} a transaction.
          </DialogDescription>
        </DialogHeader>

        <FormTransaction
          :form="form"
          v-model:displayAmount="displayAmount"
          :categories="categories"
          :tags="tags"
        />

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline"> Cancel </Button>
          </DialogClose>
          <Button type="button" @click="handleSubmit" :disabled="form.processing">
            {{ submitButtonText }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
