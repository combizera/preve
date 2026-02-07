<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
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
import { FREQUENCY_TYPE } from '@/enums/frequency-type';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import type { ICategory } from '@/types/models/category';
import type { IRecurringTransaction } from '@/types/models/recurring-transaction';
import type { ITag } from '@/types/models/tag';
import { filterNumericInput } from '@/utils/numericInput';

interface Props {
  form: InertiaForm<IRecurringTransaction>;
  categories: ICategory[];
  tags: ITag[];
}

const props = defineProps<Props>();

const displayAmount = defineModel<string>('displayAmount', { required: true });

const filteredCategories = computed(() => {
  return props.categories.filter(
    (category) => category.type === props.form.type,
  );
});
</script>

<template>
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

  <div class="grid grid-cols-3 gap-4">
    <!-- Name -->
    <div class="col-span-2 grid gap-3">
      <Label for="description"> Name </Label>
      <Input
        id="description"
        placeholder="e.g., Netflix Subscription, Rent, Salary..."
        v-model="form.description"
      />
      <InputError :message="form.errors.description" />
    </div>

    <!-- Amount -->
    <div class="grid gap-3">
      <Label for="amount"> Amount </Label>
      <InputGroup>
        <InputGroupAddon>
          <InputGroupText>R$</InputGroupText>
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
            <SelectLabel> Category</SelectLabel>
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

    <div class="grid gap-3">
      <Label for="tag" class="text-muted-foreground"> Tag (optional) </Label>
      <Select v-model="form.tag_id">
        <SelectTrigger class="w-full">
          <SelectValue placeholder="Select a tag" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>Tag</SelectLabel>
            <SelectItem :value="null">None</SelectItem>
            <SelectItem v-for="tag in tags" :value="tag.id" :key="tag.id">
              {{ tag.name }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
      <InputError :message="form.errors.tag_id" />
    </div>
  </div>

  <!-- Recurrence Settings -->
  <hr class="mt-2" />
  <div class="grid gap-3">
    <Label class="text-sm font-semibold text-muted-foreground">
      Recurrence Settings
    </Label>

    <div class="grid grid-cols-2 gap-4">
      <div class="grid gap-3">
        <Label for="frequency"> Frequency </Label>
        <Select v-model="form.frequency">
          <SelectTrigger class="w-full">
            <SelectValue placeholder="Select frequency" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Frequency</SelectLabel>
              <SelectItem :value="FREQUENCY_TYPE.MONTHLY">Monthly</SelectItem>
              <SelectItem :value="FREQUENCY_TYPE.YEARLY">Yearly</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.frequency" />
      </div>

      <div class="grid gap-3">
        <Label for="day_of_month"> Day of Month </Label>
        <Input
          id="day_of_month"
          type="number"
          min="1"
          max="31"
          placeholder="1-31"
          v-model.number="form.day_of_month"
        />
        <InputError :message="form.errors.day_of_month" />
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="grid gap-3">
        <Label for="start_date"> Start Date </Label>
        <Input
          id="start_date"
          type="date"
          v-model="form.start_date"
          class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
        />
        <InputError :message="form.errors.start_date" />
      </div>

      <div class="grid gap-3">
        <Label for="end_date" class="text-muted-foreground">
          End Date (Optional)
        </Label>
        <Input
          id="end_date"
          type="date"
          v-model="form.end_date"
          class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
        />
        <InputError :message="form.errors.end_date" />
      </div>
    </div>
  </div>
</template>
