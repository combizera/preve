<script setup lang="ts">
import { useI18n } from 'vue-i18n';

import TableCategory from '@/components/Category/TableCategory.vue';
import EmptyState from '@/components/EmptyState.vue';
import type { ICategory } from '@/types/models/category';

const { t } = useI18n();

interface Props {
  incomeCategories: ICategory[];
  expenseCategories: ICategory[];
}

defineProps<Props>();
</script>

<template>
  <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
    <!-- TABLE - CRUD -->
    <div>
      <EmptyState
        v-if="incomeCategories.length === 0"
        :title="t('categories.empty.income.title')"
        :description="t('categories.empty.income.description')"
        :showButton="false"
      />

      <TableCategory type="income" :categories="incomeCategories" v-else />
    </div>

    <div>
      <EmptyState
        v-if="expenseCategories.length === 0"
        :title="t('categories.empty.expense.title')"
        :description="t('categories.empty.expense.description')"
        :showButton="false"
      />

      <TableCategory :categories="expenseCategories" v-else />
    </div>
  </div>
</template>
