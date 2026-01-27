<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { provide, ref } from 'vue';

import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import ContainerTransactions from '@/components/Transaction/ContainerTransactions.vue';
import CreateTransactionDialog from '@/components/Transaction/CreateTransactionDialog.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import transactionRoutes from '@/routes/transactions';
import type { BreadcrumbItem } from '@/types';
import type { ICategory } from '@/types/models/category';
import type { ITag } from '@/types/models/tag';
import { ITransaction } from '@/types/models/transaction';

const props = defineProps<{
  transactions: ITransaction[];
  categories: ICategory[];
  tags: ITag[];
}>();

// Provide categories and tags to all child components
provide('categories', props.categories);
provide('tags', props.tags);

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
    title: 'Transactions',
    href: transactionRoutes.index().url,
  },
];
</script>

<template>
  <Head title="Transaction" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="mx-auto flex h-full w-full max-w-[1500px] flex-1 flex-col gap-2 overflow-x-auto rounded-xl p-4"
    >
      <!-- HEADING -->
      <Heading
        title="Transaction"
        description="Manage your transactions here."
        :hasActions="true"
      >
        <Button type="button" @click="openCreateDialog"> Create </Button>
      </Heading>

      <ContainerTransactions :transactions="transactions" />

      <!-- CREATE -->
      <CreateTransactionDialog
        v-if="showCreateDialog"
        v-model:open="showCreateDialog"
      />

      <!-- PLACEHOLDER -->
      <div
        class="relative min-h-full flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
      >
        <PlaceholderPattern />
      </div>
    </div>
  </AppLayout>
</template>
