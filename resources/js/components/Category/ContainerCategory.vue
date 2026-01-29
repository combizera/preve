<script setup lang="ts">
import TableCategory from '@/components/Category/TableCategory.vue';
import EmptyState from '@/components/EmptyState.vue';
import type { ICategory } from '@/types/models/category';

interface Props {
  incomeCategories: ICategory[];
  expenseCategories: ICategory[];
}

defineProps<Props>();
</script>

<template>
  <div class="mt-10 grid grid-cols-1 gap-4 md:grid-cols-2">
    <!-- TABLE - CRUD -->
    <div>
      <EmptyState
        v-if="incomeCategories.length === 0"
        title="No income categories yet"
        description="Start by creating your first income category"
        :showButton="false"
      />

      <TableCategory type="income" :categories="incomeCategories" v-else />
    </div>

    <div>
      <EmptyState
        v-if="expenseCategories.length === 0"
        title="No expense categories yet"
        description="Start by creating your first expense category"
        :showButton="false"
      />

      <TableCategory :categories="expenseCategories" v-else />
    </div>
  </div>
</template>
