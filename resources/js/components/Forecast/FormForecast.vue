<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import BudgetFormFields from '@/components/Forecast/BudgetFormFields.vue';
import InputError from '@/components/InputError.vue';
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
import type { ICategory } from '@/types/models/category';
import type { IForecastInput } from '@/types/models/forecast';

defineProps<{
  form: InertiaForm<IForecastInput>;
  categories: ICategory[];
  editMode?: boolean;
  lockCategory?: boolean;
}>();

const { t } = useI18n();
</script>

<template>
  <div class="grid gap-4">
    <div class="grid grid-cols-2 gap-4">
      <div class="grid gap-3">
        <Label for="category">{{ t('models.category.name') }}</Label>
        <Select v-model="form.category_id" :disabled="editMode || lockCategory">
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
        <Input
          id="month"
          type="month"
          v-model="form.month"
          :disabled="editMode"
          class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
        />
        <InputError :message="form.errors.month" />
      </div>
    </div>

    <BudgetFormFields :form="form" :show-apply-to-default="editMode" />
  </div>
</template>
