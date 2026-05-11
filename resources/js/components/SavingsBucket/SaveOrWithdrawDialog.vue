<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { getLocalTimeZone, today } from '@internationalized/date';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
  InputGroupText,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import {
  extractNumbers,
  formatCentsToDisplay,
  getCurrencySymbol,
  parseToCents,
} from '@/lib/currency';
import { store } from '@/routes/transactions';
import type { ICategory } from '@/types/models/category';
import type { ISavingsBucket } from '@/types/models/savings-bucket';
import type { ITransactionInput } from '@/types/models/transaction';
import { filterNumericInput } from '@/utils/numericInput';
import { validateAmount } from '@/utils/validateAmount';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  savingsBuckets: ISavingsBucket[];
  categories: ICategory[];
}>();

const { t } = useI18n();

const rawAmount = ref('');

const form = useForm<ITransactionInput>({
  category_id: 0,
  tags: [],
  savings_bucket_id: null,
  amount: 0,
  type: TRANSACTION_TYPE.EXPENSE,
  description: '',
  notes: null,
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

const filteredCategories = computed(() =>
  props.categories.filter((category) => category.type === form.type),
);

watch(
  () => form.type,
  () => {
    const stillValid = filteredCategories.value.some(
      (c) => c.id === form.category_id,
    );
    if (!stillValid) form.category_id = 0;
  },
);

const submit = () => {
  if (!validateAmount(form, t)) return;

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
    <DialogContent class="sm:max-w-137.5">
      <DialogHeader>
        <DialogTitle>
          {{ t('savings.saveOrWithdraw.dialog.title') }}
        </DialogTitle>
        <DialogDescription>
          {{ t('savings.saveOrWithdraw.dialog.description') }}
        </DialogDescription>
      </DialogHeader>

      <!-- Direction -->
      <div class="grid gap-3">
        <ToggleGroup v-model="form.type" class="w-full">
          <ToggleGroupItem
            :value="TRANSACTION_TYPE.EXPENSE"
            class="flex-1 gap-2"
          >
            <ArrowUpRight :size="16" />
            {{ t('savings.saveOrWithdraw.deposit') }}
          </ToggleGroupItem>
          <ToggleGroupItem
            :value="TRANSACTION_TYPE.INCOME"
            class="flex-1 gap-2"
          >
            <ArrowDownLeft :size="16" />
            {{ t('savings.saveOrWithdraw.withdraw') }}
          </ToggleGroupItem>
        </ToggleGroup>
        <p class="text-xs text-muted-foreground">
          {{
            form.type === TRANSACTION_TYPE.EXPENSE
              ? t('savings.saveOrWithdraw.depositHint')
              : t('savings.saveOrWithdraw.withdrawHint')
          }}
        </p>
      </div>

      <!-- Bucket -->
      <div class="grid gap-3">
        <Label for="savings_bucket_id">
          {{ t('savings.saveOrWithdraw.bucket') }}
        </Label>
        <Select
          :model-value="
            form.savings_bucket_id ? String(form.savings_bucket_id) : ''
          "
          @update:model-value="
            (value) => (form.savings_bucket_id = Number(value))
          "
        >
          <SelectTrigger class="w-full">
            <SelectValue
              :placeholder="t('savings.saveOrWithdraw.bucketPlaceholder')"
            />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>{{ t('savings.title') }}</SelectLabel>
              <SelectItem
                v-for="bucket in savingsBuckets"
                :value="String(bucket.id)"
                :key="bucket.id"
              >
                {{ bucket.name }}
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.savings_bucket_id" />
      </div>

      <!-- Amount & Date -->
      <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-3">
          <Label for="amount">{{ t('models.transaction.amount') }}</Label>
          <InputGroup>
            <InputGroupAddon>
              <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
            </InputGroupAddon>
            <InputGroupInput
              id="amount"
              type="text"
              inputmode="numeric"
              placeholder="0,00"
              v-model="displayAmount"
              @keydown="filterNumericInput"
            />
          </InputGroup>
          <InputError :message="form.errors.amount" />
        </div>

        <div class="grid gap-3">
          <Label for="transaction_date">
            {{ t('models.transaction.date') }}
          </Label>
          <DatePicker
            id="transaction_date"
            v-model="form.transaction_date"
            class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
          />
          <InputError :message="form.errors.transaction_date" />
        </div>
      </div>

      <!-- Category -->
      <div class="grid gap-3">
        <Label for="category">{{ t('models.category.name') }}</Label>
        <Select v-model="form.category_id">
          <SelectTrigger class="w-full">
            <SelectValue
              :placeholder="t('generic.placeholders.selectCategory')"
            />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>{{ t('models.category.name') }}</SelectLabel>
              <SelectItem
                v-for="category in filteredCategories"
                :value="category.id"
                :key="category.id"
              >
                {{ category.name }}
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.category_id" />
      </div>

      <!-- Description -->
      <div class="grid gap-3">
        <Label for="description">
          {{ t('models.transaction.description') }}
        </Label>
        <Input
          id="description"
          :placeholder="t('generic.placeholders.transactionDescription')"
          v-model="form.description"
        />
        <InputError :message="form.errors.description" />
      </div>

      <!-- Notes -->
      <div class="grid gap-3">
        <Label for="notes" class="text-muted-foreground">
          {{ t('generic.labels.notesOptional') }}
        </Label>
        <Input
          id="notes"
          :placeholder="t('generic.placeholders.additionalNotes')"
          v-model="form.notes"
        />
        <InputError :message="form.errors.notes" />
      </div>

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
        </DialogClose>
        <Button type="button" @click="submit" :disabled="form.processing">
          {{
            form.type === TRANSACTION_TYPE.EXPENSE
              ? t('savings.saveOrWithdraw.deposit')
              : t('savings.saveOrWithdraw.withdraw')
          }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
