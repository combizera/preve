<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';

import Heading from '@/components/Heading.vue';
import ContainerRecurrings from '@/components/Recurring/ContainerRecurrings.vue';
import CreateRecurringDialog from '@/components/Recurring/CreateRecurringDialog.vue';
import RecurringCards from '@/components/Recurring/RecurringCards.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { useRecurringStore } from '@/stores/recurring.store';
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

const recurringStore = useRecurringStore();
const { showFormDialog } = storeToRefs(recurringStore)

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
    <!-- HEADING -->
    <Heading
      title="Recurring Transactions"
      description="Manage your recurring transactions here."
      :hasActions="true"
    >
      <Button type="button" @click="recurringStore.openCreateDialog()">
        Create
      </Button>
    </Heading>

    <!-- RECURRING CARDS -->
    <RecurringCards
      :incomeRecurring="incomeRecurring"
      :expenseRecurring="expenseRecurring"
    />

    <!-- CONTAINER -->
    <div class="mt-6 space-y-6">
      <ContainerRecurrings
        :tags="tags"
        :categories="categories"
        :recurringTransactions="incomeRecurring"
        type="income"
      />

      <ContainerRecurrings
        :tags="tags"
        :categories="categories"
        :recurringTransactions="expenseRecurring"
        type="expense"
      />
    </div>

    <!-- CREATE -->
    <CreateRecurringDialog
      v-if="showFormDialog"
      :categories="categories"
      :tags="tags"
    />
  </AppLayout>
</template>
