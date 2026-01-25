<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

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
import { store } from '@/routes/transactions';
import { ICategory } from '@/types/models/category';
import { ITag } from '@/types/models/tag';
import { ITransaction } from '@/types/models/transaction';

const open = defineModel<boolean>('open', { required: true });

defineProps<{
  categories: ICategory[];
  tags: ITag[];
}>();

const form = useForm<ITransaction>({
  category_id: 0,
  tag_id: 0,
  amount: 0,
  type: TRANSACTION_TYPE.EXPENSE,
  description: '',
  notes: '' | null,
  transaction_date: new Date().toISOString().split('T')[0],
});

const createTransaction = () => {
  form.submit(store(), {
    onSuccess: () => {
      open.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <Dialog v-model:open="open">
    <form>
      <DialogContent class="sm:max-w-[550px]">
        <DialogHeader>
          <DialogTitle>Create Transaction</DialogTitle>
          <DialogDescription>
            Fill in the details below to create a new transaction.
          </DialogDescription>
        </DialogHeader>

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
            <Label for="tag"> Tag </Label>
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

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-3">
            <Label for="amount"> Amount </Label>
            <Input
              id="amount"
              type="number"
              step="0.01"
              placeholder="0.00"
              v-model.number="form.amount"
            />
            <InputError :message="form.errors.amount" />
          </div>

          <div class="grid gap-3">
            <Label for="type"> Type </Label>
            <ToggleGroup v-model="form.type" class="w-full">
              <ToggleGroupItem :value="TRANSACTION_TYPE.INCOME" class="flex-1">
                Income
              </ToggleGroupItem>
              <ToggleGroupItem :value="TRANSACTION_TYPE.EXPENSE" class="flex-1">
                Expense
              </ToggleGroupItem>
            </ToggleGroup>
            <InputError :message="form.errors.type" />
          </div>
        </div>

        <div class="grid gap-4">
          <div class="grid gap-3">
            <Label for="description"> Description </Label>
            <Input
              id="description"
              placeholder="Enter transaction description"
              v-model="form.description"
            />
            <InputError :message="form.errors.description" />
          </div>

          <div class="grid gap-3">
            <Label for="transaction_date"> Date </Label>
            <Input
              id="transaction_date"
              type="date"
              v-model="form.transaction_date"
            />
            <InputError :message="form.errors.transaction_date" />
          </div>

          <div class="grid gap-3">
            <Label for="notes"> Notes (Optional) </Label>
            <Input
              id="notes"
              placeholder="Additional notes..."
              v-model="form.notes"
            />
            <InputError :message="form.errors.notes" />
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline"> Cancel </Button>
          </DialogClose>
          <Button
            type="button"
            @click="createTransaction"
            :disabled="form.processing"
          >
            Create Transaction
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
