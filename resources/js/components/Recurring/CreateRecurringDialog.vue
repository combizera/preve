<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { today, getLocalTimeZone } from '@internationalized/date';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

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
import {
  extractNumbers,
  formatCentsToDisplay,
  parseToCents,
} from '@/lib/currency';
import { store } from '@/routes/recurring';
import { useRecurringStore } from '@/stores/recurring.store';
import { type ICategory } from '@/types/models/category';
import type { IRecurringTransactionInput } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';
import { validateAmount } from '@/utils/validateAmount';

interface Props {
  categories: ICategory[];
  tags: ITag[];
}

defineProps<Props>();

const { t } = useI18n();
const recurringStore = useRecurringStore();
const { showFormDialog, recurringType } = storeToRefs(recurringStore);

const rawAmount = ref('');

const form = useForm<IRecurringTransactionInput>({
  category_id: 0,
  tags: [],
  amount: 0,
  frequency: FREQUENCY_TYPE.MONTHLY,
  type: recurringType.value,
  description: '',
  is_active: true,
  day_of_month: new Date().getDate(),
  start_date: today(getLocalTimeZone()).toString(),
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
  if (!validateAmount(form, t)) return;

  form.submit(store(), {
    onSuccess: () => {
      recurringStore.closeCreateDialog();
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
          <DialogTitle>{{ t('recurring.create.title') }}</DialogTitle>
          <DialogDescription>
            {{ t('recurring.create.description') }}
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
            <Button variant="outline"> {{ t('generic.actions.cancel') }} </Button>
          </DialogClose>
          <Button
            type="button"
            @click="createRecurring"
            :disabled="form.processing"
          >
            {{ t('recurring.create.button') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
