<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import FormRecurring from '@/components/Recurring/FormRecurring.vue';
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
import { FREQUENCY_TYPE } from '@/enums/frequency-type';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import {
  extractNumbers,
  formatCentsToDisplay,
  parseToCents,
} from '@/lib/currency';
import { store } from '@/routes/recurring';
import { type ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';
import { formatTransactionDate } from '@/utils/formatDate';

const open = defineModel<boolean>('open', { required: true });

interface Props {
  categories: ICategory[];
  tags: ITag[];
}

defineProps<Props>();

const rawAmount = ref('');

const form = useForm<IRecurringTransaction>({
  category_id: 0,
  tag_id: undefined,
  amount: 0,
  frequency: FREQUENCY_TYPE.MONTHLY,
  type: TRANSACTION_TYPE.EXPENSE,
  description: '',
  is_active: true,
  day_of_month: new Date().getDate(),
  start_date: formatTransactionDate(),
  end_date: undefined,
});

const displayAmount = computed({
  get: () => formatCentsToDisplay(rawAmount.value),
  set: (value: string) => {
    const numbers = extractNumbers(value);
    rawAmount.value = numbers;
    form.amount = parseToCents(numbers);
  },
});

const createRecurring = () => {
  form.submit(store(), {
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
      <DialogContent class="sm:max-w-137.5">
        <DialogHeader>
          <DialogTitle>Create Recurring Transaction</DialogTitle>
          <DialogDescription>
            Fill in the details below to create a new recurring transaction.
          </DialogDescription>
        </DialogHeader>

        <FormRecurring
          :form="form"
          v-model:displayAmount="displayAmount"
          :categories="categories"
          :tags="tags"
        />

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline"> Cancel </Button>
          </DialogClose>
          <Button
            type="button"
            @click="createRecurring"
            :disabled="form.processing"
          >
            Create Recurring
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
