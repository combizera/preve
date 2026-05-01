<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import type { ICategoryForm } from '@/types/models/category';

defineProps<{
  form: InertiaForm<ICategoryForm>;
}>();

const { t } = useI18n();
</script>

<template>
  <div class="grid gap-4">
    <!-- Type -->
    <div class="grid gap-3">
      <Label>{{ t('models.transaction.type') }}</Label>
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

    <!-- Name -->
    <div class="grid gap-3">
      <Label for="name">{{ t('generic.labels.name') }}</Label>
      <Input
        id="name"
        name="name"
        :placeholder="t('generic.placeholders.categoryName')"
        v-model="form.name"
      />
      <InputError :message="form.errors.name" />
    </div>

    <!-- Description -->
    <div class="grid gap-3">
      <Label for="description">{{ t('models.transaction.description') }}</Label>
      <Input
        id="description"
        name="description"
        :placeholder="t('generic.placeholders.categoryDescription')"
        v-model="form.description"
      />
      <InputError :message="form.errors.description" />
    </div>

    <slot />
  </div>
</template>
