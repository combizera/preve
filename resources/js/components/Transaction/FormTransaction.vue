<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { DatePicker } from '@/components/ui/date-picker';
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
import { getCurrencySymbol } from '@/lib/currency';
import type { ICategory } from '@/types/models/category';
import type { ITag } from '@/types/models/tag';
import type { ITransaction } from '@/types/models/transaction';
import { filterNumericInput } from '@/utils/numericInput';

interface Props {
  form: InertiaForm<ITransaction>;
  categories: ICategory[];
  tags: ITag[];
}

const props = defineProps<Props>();

const { t } = useI18n();

const displayAmount = defineModel<string>('displayAmount', { required: true });
const currencySymbol = getCurrencySymbol();

const filteredCategories = computed(() => {
  return props.categories.filter(
    (category) => category.type === props.form.type,
  );
});
</script>

<template>
  <!-- Type -->
  <div class="grid gap-3">
    <Label for="type"> {{ t('models.transaction.type') }} </Label>
    <ToggleGroup v-model="form.type" class="w-full">
      <ToggleGroupItem :value="TRANSACTION_TYPE.EXPENSE" class="flex-1 gap-2">
        <ArrowUpRight :size="16" />
        {{ t('models.transaction.expense') }}
      </ToggleGroupItem>
      <ToggleGroupItem :value="TRANSACTION_TYPE.INCOME" class="flex-1 gap-2">
        <ArrowDownLeft :size="16" />
        {{ t('models.transaction.income') }}
      </ToggleGroupItem>
    </ToggleGroup>
    <InputError :message="form.errors.type" />
  </div>

  <!-- Amount & Date -->
  <div class="grid grid-cols-2 gap-4">
    <div class="grid gap-3">
      <Label for="amount"> {{ t('models.transaction.amount') }} </Label>
      <InputGroup>
        <InputGroupAddon>
          <InputGroupText>{{ currencySymbol }}</InputGroupText>
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
      <Label for="transaction_date"> {{ t('models.transaction.date') }} </Label>
      <DatePicker
        id="transaction_date"
        v-model="form.transaction_date"
        class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
      />
      <InputError :message="form.errors.transaction_date" />
    </div>
  </div>

  <!-- Category & Tag -->
  <div class="grid grid-cols-2 gap-4">
    <div class="grid gap-3">
      <Label for="category"> {{ t('models.category.name') }} </Label>
      <Select v-model="form.category_id">
        <SelectTrigger class="w-full">
          <SelectValue :placeholder="t('generic.placeholders.selectCategory')" />
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

    <div class="grid gap-3">
      <Label for="tag" class="text-muted-foreground"> {{ t('models.tag.optional') }} </Label>
      <Select v-model="form.tag_id">
        <SelectTrigger class="w-full">
          <SelectValue :placeholder="t('generic.placeholders.selectTag')" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>{{ t('models.tag.name') }}</SelectLabel>
            <SelectItem :value="null">{{ t('generic.labels.none') }}</SelectItem>
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
    <Label for="description"> {{ t('models.transaction.description') }} </Label>
    <Input
      id="description"
      :placeholder="t('generic.placeholders.transactionDescription')"
      v-model="form.description"
    />
    <InputError :message="form.errors.description" />
  </div>

  <!-- Notes -->
  <div class="grid gap-3">
    <Label for="notes" class="text-muted-foreground"> {{ t('generic.labels.notesOptional') }} </Label>
    <Input id="notes" :placeholder="t('generic.placeholders.additionalNotes')" v-model="form.notes" />
    <InputError :message="form.errors.notes" />
  </div>
</template>
