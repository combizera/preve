<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { today, getLocalTimeZone } from '@internationalized/date';
import { computed, inject, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

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
import type { ITransaction, ITransactionInput } from '@/types/models/transaction';
import { validateAmount } from '@/utils/validateAmount';

const open = defineModel<boolean>('open', { required: true });

interface Props {
  transaction: ITransaction;
  type: 'edit' | 'duplicate';
}

const props = defineProps<Props>();

const { t } = useI18n();
const categories = inject<ICategory[]>('categories', []);
const tags = inject<ITag[]>('tags', []);

const rawAmount = ref('');

const form = useForm<ITransactionInput>({
  category_id: 0,
  tags: [],
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
      form.tags = transaction.tags?.map((tag) => tag.id) ?? [];
      form.amount = amount;
      form.type = transaction.type ?? TRANSACTION_TYPE.EXPENSE;
      form.description = transaction.description ?? '';
      form.notes = transaction.notes ?? '';
      form.transaction_date = transaction.transaction_date
        ? transaction.transaction_date.split('T')[0]
        : today(getLocalTimeZone()).toString();
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
  if (!validateAmount(form, t)) return;

  form.submit(store(), {
    onSuccess: () => {
      open.value = false;
      form.reset();
      rawAmount.value = '';
    },
  });
};

const updateTransaction = () => {
  const transactionId = props.transaction.id;

  if (!transactionId) {
    return;
  }

  if (!validateAmount(form, t)) return;

  form.submit(update(transactionId), {
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
  return props.type === 'duplicate'
    ? t('transactions.duplicate.title')
    : t('transactions.edit.title');
});
</script>

<template>
  <Dialog v-model:open="open">
    <form>
      <DialogContent class="sm:max-w-137.5">
        <DialogHeader>
          <DialogTitle>
            {{ type === 'duplicate' ? t('transactions.duplicate.title') : t('transactions.edit.title') }}
          </DialogTitle>
          <DialogDescription>
            {{ type === 'duplicate' ? t('transactions.duplicate.description') : t('transactions.edit.description') }}
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
            <Button variant="outline"> {{ t('generic.actions.cancel') }} </Button>
          </DialogClose>
          <Button
            type="button"
            @click="handleSubmit"
            :disabled="form.processing"
          >
            {{ submitButtonText }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
