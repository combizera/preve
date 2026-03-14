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

const { t } = useI18n();

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

  <div class="grid grid-cols-3 gap-4">
    <!-- Name -->
    <div class="col-span-2 grid gap-3">
      <Label for="description"> {{ t('generic.labels.name') }} </Label>
      <Input
        id="description"
        :placeholder="t('generic.placeholders.recurringDescription')"
        v-model="form.description"
      />
      <InputError :message="form.errors.description" />
    </div>

    <!-- Amount -->
    <div class="grid gap-3">
      <Label for="amount"> {{ t('models.transaction.amount') }} </Label>
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

  <!-- Recurrence Settings -->
  <hr class="mt-2" />
  <div class="grid gap-3">
    <Label class="text-sm font-semibold text-muted-foreground">
      {{ t('recurring.form.recurrenceSettings') }}
    </Label>

    <div class="grid grid-cols-2 gap-4">
      <div class="grid gap-3">
        <Label for="frequency"> {{ t('recurring.form.frequency') }} </Label>
        <Select v-model="form.frequency">
          <SelectTrigger class="w-full">
            <SelectValue :placeholder="t('generic.placeholders.selectFrequency')" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>{{ t('recurring.form.frequency') }}</SelectLabel>
              <SelectItem :value="FREQUENCY_TYPE.MONTHLY">{{ t('recurring.form.monthly') }}</SelectItem>
              <SelectItem :value="FREQUENCY_TYPE.YEARLY">{{ t('recurring.form.yearly') }}</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.frequency" />
      </div>

      <div class="grid gap-3">
        <Label for="day_of_month"> {{ t('recurring.form.dayOfMonth') }} </Label>
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
        <Label for="start_date"> {{ t('generic.labels.startDate') }} </Label>
        <DatePicker
          id="start_date"
          v-model="form.start_date"
          class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
        />
        <InputError :message="form.errors.start_date" />
      </div>

      <div class="grid gap-3">
        <Label for="end_date" class="text-muted-foreground">
          {{ t('generic.labels.endDateOptional') }}
        </Label>
        <DatePicker
          id="end_date"
          v-model="form.end_date"
          class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
        />
        <InputError :message="form.errors.end_date" />
      </div>
    </div>
  </div>
</template>
