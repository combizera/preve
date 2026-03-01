<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import Heading from '@/components/Heading.vue';
import ContainerTransactions from '@/components/Transaction/ContainerTransactions.vue';
import CreateTransactionDialog from '@/components/Transaction/CreateTransactionDialog.vue';
import FilterTransaction from '@/components/Transaction/FilterTransaction.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import transactionRoutes from '@/routes/transactions';
import type { BreadcrumbItem } from '@/types';
import { ITransaction } from '@/types/models/transaction';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatCentsToDisplay } from '@/lib/currency';
import { formatTransactionDate } from '@/utils/formatDate';
import { cn } from '@/lib/utils';

interface Props {
  transaction: ITransaction;
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
  {
    title: 'Transaction Details',
    href: '',
  },
];
function formattedAmount(transaction: ITransaction) {
  return formatCentsToDisplay(transaction.amount);
}

function amountClass(transaction: ITransaction) {
  return cn(
    'text-foreground text-md font-bold',
    transaction.type === 'expense'
      ? "text-destructive before:content-['-']"
      : 'text-positive',
  );
}
</script>

<template>
  <Head title="Transaction" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="page-container">
      <!-- HEADING -->
      <Heading
        title="Transaction Details"
        description="View the details of your transaction here."
      />

      <Card class="gap-0 min-w-lg mx-auto">
        <CardContent class="-mb-4">
          <h2 class="text-lg font-medium">
            {{ transaction.description }}
          </h2>
          <div class="bg-muted rounded-lg p-4 my-4 space-y-4">
            <p class="text-lg font-medium">
              Details
            </p>

            <ul class="space-y-2">
              <li class="flex justify-between gap-2">
                <span class="text-muted-foreground text-sm">
                  Date:
                </span>
                <p class="text-foreground text-sm">
                  {{ formatTransactionDate(transaction.transaction_date) }}
                </p>
              </li>
              <li class="flex justify-between gap-2">
                <span class="text-muted-foreground text-sm">
                  Category:
                </span>
                <p class="text-foreground text-sm">
                  {{ transaction.category?.name }}
                </p>
              </li>
              <li v-if="transaction.tag" class="flex justify-between gap-2">
                <span class="text-muted-foreground text-sm">
                  Tag:
                </span>
                <p class="text-foreground text-sm">
                  {{ transaction.tag?.name }}
                </p>
              </li>
              <li v-if="transaction.notes" class="flex justify-between gap-2">
                <span class="text-muted-foreground text-sm">
                  Notes:
                </span>
                <p class="text-foreground text-sm">
                  {{ transaction.notes }}
                </p>
              </li>
              <li class="flex justify-between gap-2">
                <span class="text-muted-foreground text-sm">
                  Created At:
                </span>
                <p class="text-foreground text-sm">
                  {{ formatTransactionDate(transaction.created_at) }}
                </p>
              </li>
            </ul>

            <div class="w-full h-px border-b border-dashed border-muted-foreground/40" />

            <ul class="space-y-2">
              <li class="flex justify-between gap-2">
                <span class="text-muted-foreground text-md font-medium">
                  Amount:
                </span>
                <p :class="amountClass(transaction)">
                  R$ {{ formattedAmount(transaction) }}
                </p>
              </li>
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
