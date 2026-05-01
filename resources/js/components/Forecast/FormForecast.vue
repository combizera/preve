<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
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
import { getCurrencySymbol } from '@/lib/currency';
import type { ICategory } from '@/types/models/category';
import type { IForecastInput } from '@/types/models/forecast';
import { filterNumericInput } from '@/utils/numericInput';

interface Props {
  form: InertiaForm<IForecastInput>;
  categories: ICategory[];
  editMode?: boolean;
}

defineProps<Props>();

const { t } = useI18n();

const displayAmount = defineModel<string>('displayAmount', { required: true });
const currencySymbol = getCurrencySymbol();
</script>

<template>
  <!-- Category & Month -->
  <div class="grid grid-cols-2 gap-4">
    <div class="grid gap-3">
      <Label for="category">{{ t('models.category.name') }}</Label>
      <Select v-model="form.category_id" :disabled="editMode">
        <SelectTrigger class="w-full">
          <SelectValue :placeholder="t('generic.placeholders.selectCategory')" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>{{ t('models.category.name') }}</SelectLabel>
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
      <Label for="month">{{ t('forecasts.form.month') }}</Label>
      <Input id="month" type="month" v-model="form.month" :disabled="editMode" class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0" />
      <InputError :message="form.errors.month" />
    </div>
  </div>

  <!-- Amount -->
  <div class="grid gap-3">
    <Label for="amount">{{ t('forecasts.form.amount') }}</Label>
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

  <!-- Notes -->
  <div class="grid gap-3">
    <Label for="notes" class="text-muted-foreground">{{ t('forecasts.form.notes') }}</Label>
    <Input id="notes" :placeholder="t('generic.placeholders.additionalNotes')" v-model="form.notes" />
    <InputError :message="form.errors.notes" />
  </div>

  <!-- Apply to default (edit mode only) -->
  <div v-if="editMode" class="flex items-start gap-3 rounded-md border border-border bg-muted/30 p-3">
    <Checkbox
      id="apply_to_default"
      :model-value="form.apply_to_default ?? false"
      @update:model-value="(value) => (form.apply_to_default = !!value)"
      class="mt-0.5"
    />
    <div class="grid gap-1">
      <Label for="apply_to_default" class="cursor-pointer text-sm leading-none font-medium">
        {{ t('forecasts.form.applyToDefault') }}
      </Label>
      <p class="text-xs text-muted-foreground">
        {{ t('forecasts.form.applyToDefaultHelp') }}
      </p>
    </div>
  </div>
</template>
