<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { today, getLocalTimeZone } from '@internationalized/date';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
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
import { store } from '@/routes/transactions';
import { useTransactionStore } from '@/stores/transaction.store';
import type { ICategory } from '@/types/models/category';
import type { ITag } from '@/types/models/tag';
import { ITransaction } from '@/types/models/transaction';
import { validateAmount } from '@/utils/validateAmount';

interface Props {
  categories: ICategory[];
  tags: ITag[];
}

defineProps<Props>();

const { t } = useI18n();
const transactionStore = useTransactionStore();

const { showFormDialog } = storeToRefs(transactionStore);

const rawAmount = ref('');

const form = useForm<ITransaction>({
  category_id: 0,
  tag_id: undefined,
  amount: 0,
  type: TRANSACTION_TYPE.EXPENSE,
  description: '',
  notes: undefined,
  transaction_date: today(getLocalTimeZone()).toString(),
});

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
      transactionStore.closeCreateDialog();
      form.reset();
      rawAmount.value = '';
    },
  });
};
</script>

<template>
  <Dialog v-model:open="showFormDialog">
    <form>
      <DialogContent class="sm:max-w-137.5">
        <DialogHeader>
          <DialogTitle>{{ t('transactions.create.title') }}</DialogTitle>
          <DialogDescription>
            {{ t('transactions.create.description') }}
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
            @click="createTransaction"
            :disabled="form.processing"
          >
            {{ t('transactions.create.title') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
