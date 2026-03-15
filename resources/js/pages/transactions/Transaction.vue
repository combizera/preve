<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

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

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
  {
    title: t('transactions.title'),
    href: transactionRoutes.index().url,
  },
]);
</script>

<template>
  <Head :title="t('transactions.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- HEADING -->
    <Heading
      :title="t('transactions.title')"
      :description="t('transactions.description')"
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
      :transactions="transactions"
      :filters="filters"
    />

    <!-- CREATE -->
    <CreateTransactionDialog
      :categories="categories"
      :tags="tags"
    />
  </AppLayout>
</template>
