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
import {
  extractNumbers,
  formatCentsToDisplay,
  parseToCents,
} from '@/lib/currency';
import { update } from '@/routes/recurring';
import { type ICategory } from '@/types/models/category';
import { type IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';
import { formatTransactionDate } from '@/utils/formatDate';

const open = defineModel<boolean>('open', { required: true });

interface Props {
  recurringTransaction: IRecurringTransaction;
  categories: ICategory[];
  tags: ITag[];
}

const props = defineProps<Props>();

const rawAmount = ref(props.recurringTransaction.amount.toString());

const form = useForm<IRecurringTransaction>({
  ...props.recurringTransaction,
  start_date: formatTransactionDate(props.recurringTransaction.start_date),
  end_date: formatTransactionDate(props.recurringTransaction.end_date),
});

const displayAmount = computed({
  get: () => formatCentsToDisplay(rawAmount.value),
  set: (value: string) => {
    const numbers = extractNumbers(value);
    rawAmount.value = numbers;
    form.amount = parseToCents(numbers);
  },
});

const updateRecurring = () => {
  const transactionId = props.recurringTransaction.id;

  if (!transactionId) {
    return;
  }

  form.submit(update(transactionId), {
    onSuccess: () => {
      open.value = false;
    },
  });
};
</script>

<template>
  <Dialog v-model:open="open">
    <form>
      <DialogContent class="sm:max-w-137.5">
        <DialogHeader>
          <DialogTitle>Edit Recurring Transaction</DialogTitle>
          <DialogDescription>
            Make changes to {{ recurringTransaction.description }}. Click save
            when you're done.
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
            @click="updateRecurring"
            :disabled="form.processing"
          >
            Save changes
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
