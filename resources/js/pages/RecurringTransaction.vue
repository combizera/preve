<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import ContainerRecurrings from '@/components/Recurring/ContainerRecurrings.vue';
import CreateRecurringDialog from '@/components/Recurring/CreateRecurringDialog.vue';
import RecurringCards from '@/components/Recurring/RecurringCards.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ICategory } from '@/types/models/category';
import { type IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';

interface Props {
  expenseRecurring: IRecurringTransaction[];
  incomeRecurring: IRecurringTransaction[];
  categories: ICategory[];
  tags: ITag[];
}

defineProps<Props>();

const showCreateDialog = ref(false);

const openCreateDialog = () => {
  showCreateDialog.value = true;
};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Recurring',
    href: dashboard().url,
  },
];
</script>

<template>
  <Head title="Recurring" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="page-container">
      <!-- HEADING -->
      <Heading
        title="Recurring Transactions"
        description="Manage your recurring transactions here."
        :hasActions="true"
      >
        <Button type="button" @click="openCreateDialog"> Create </Button>
      </Heading>

      <!-- RECURRING CARDS -->
      <RecurringCards
        :incomeRecurring="incomeRecurring"
        :expenseRecurring="expenseRecurring"
      />

      <!-- CONTAINER -->
      <div class="space-y-10 mt-5">
        <ContainerRecurrings
          :tags="tags"
          :categories="categories"
          :recurringTransactions="incomeRecurring"
          type="income"
          @create="openCreateDialog"
        />

        <ContainerRecurrings
          :tags="tags"
          :categories="categories"
          :recurringTransactions="expenseRecurring"
          @create="openCreateDialog"
        />
      </div>

      <!-- CREATE -->
      <CreateRecurringDialog
        v-if="showCreateDialog"
        v-model:open="showCreateDialog"
        :categories="categories"
        :tags="tags"
      />
    </div>
  </AppLayout>
</template>
