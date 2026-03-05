<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import ContainerTransactions from '@/components/Transaction/ContainerTransactions.vue';
import CreateTransactionButton from '@/components/Transaction/CreateTransactionButton.vue';
import CreateTransactionDialog from '@/components/Transaction/CreateTransactionDialog.vue';
import FilterTransaction from '@/components/Transaction/FilterTransaction.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import transactionRoutes from '@/routes/transactions';
import type { BreadcrumbItem } from '@/types';
import { ITransactionFilters } from '@/types/filters';
import type { ICategory } from '@/types/models/category';
import type { ITag } from '@/types/models/tag';
import { ITransaction } from '@/types/models/transaction';

interface Props {
  transactions: ITransaction[];
  categories: ICategory[];
  tags: ITag[];
  filters: ITransactionFilters;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Transactions',
    href: transactionRoutes.index().url,
  },
];
</script>

<template>
  <Head title="Transactions" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="page-container">
      <!-- HEADING -->
      <Heading
        title="Transactions"
        description="Manage your transactions here."
        :hasActions="true"
      >
        <div class="flex items-center gap-2">
          <FilterTransaction
            :filters="filters"
            :categories="categories"
            :tags="tags"
          />
          <CreateTransactionButton />
        </div>
      </Heading>

      <!-- TRANSACTIONS -->
      <ContainerTransactions
        :transactions="[]"
        :filters="filters"
      />

      <!-- CREATE -->
      <CreateTransactionDialog
        :categories="categories"
        :tags="tags"
      />
    </div>
  </AppLayout>
</template>
