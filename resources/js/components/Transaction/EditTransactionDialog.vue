<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';
import { computed, inject, ref, watch } from 'vue';

import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
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

        <!-- Type -->
        <div class="grid gap-3">
          <Label for="type"> Type </Label>
          <ToggleGroup v-model="form.type" class="w-full">
            <ToggleGroupItem :value="TRANSACTION_TYPE.EXPENSE" class="flex-1 gap-2">
              <ArrowUpRight :size="16" />
              Expense
            </ToggleGroupItem>
            <ToggleGroupItem :value="TRANSACTION_TYPE.INCOME" class="flex-1 gap-2">
              <ArrowDownLeft :size="16" />
              Income
            </ToggleGroupItem>
          </ToggleGroup>
          <InputError :message="form.errors.type" />
        </div>

        <!-- Amount & Date -->
        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-3">
            <Label for="amount"> Amount </Label>
            <Input
              id="amount"
              type="text"
              inputmode="numeric"
              placeholder="0,00"
              v-model="displayAmount"
              @keypress="(e: KeyboardEvent) => {
                if (!/[0-9]/.test(e.key)) {
                  e.preventDefault();
                }
              }"
              class="text-right font-mono"
            />
            <InputError :message="form.errors.amount" />
          </div>

          <div class="grid gap-3">
            <Label for="transaction_date"> Date </Label>
            <Input
              id="transaction_date"
              type="date"
              v-model="form.transaction_date"
              class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
            />
            <InputError :message="form.errors.transaction_date" />
          </div>
        </div>

        <!-- Category & Tag -->
        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-3">
            <Label for="category"> Category </Label>
            <Select v-model="form.category_id">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select a category" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Category</SelectLabel>
                  <SelectItem
                    v-for="category in categories"
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

          <div class="grid gap-3">
            <Label for="tag" class="text-muted-foreground"> Tag (Optional) </Label>
            <Select v-model="form.tag_id">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select a tag" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Tag</SelectLabel>
                  <SelectItem v-for="tag in tags" :value="tag.id" :key="tag.id">
                    {{ tag.name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.tag_id" />
          </div>
        </div>

        <!-- Description -->
        <div class="grid gap-3">
          <Label for="description"> Description </Label>
          <Input
            id="description"
            placeholder="Enter transaction description"
            v-model="form.description"
          />
          <InputError :message="form.errors.description" />
        </div>

        <!-- Notes -->
        <div class="grid gap-3">
          <Label for="notes" class="text-muted-foreground"> Notes (Optional) </Label>
          <Input
            id="notes"
            placeholder="Additional notes..."
            v-model="form.notes"
          />
          <InputError :message="form.errors.notes" />
        </div>

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
