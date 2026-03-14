<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import BalanceCards from '@/components/Dashboard/BalanceCards.vue';
import ChartMonthly from '@/components/Dashboard/ChartMonthly.vue';
import HorizontalCalendarStrip from '@/components/Dashboard/HorizontalCalendarStrip.vue';
import LastTransactionsTable from '@/components/Dashboard/LastTransactionsTable.vue';
import Heading from '@/components/Heading.vue';
import CreateTransactionButton from '@/components/Transaction/CreateTransactionButton.vue';
import CreateTransactionDialog from '@/components/Transaction/CreateTransactionDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ICategory } from '@/types/models/category';
import { type ITag } from '@/types/models/tag';
import { type IDailyBalance, type ITransaction } from '@/types/models/transaction';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
];

interface Props {
  latestTransactions: ITransaction[];
  availableBalance: number;
  forecast: number;
  monthlyIncome: number;
  monthlyExpenses: number;
  dailyBalances: IDailyBalance[];
  carryOver: number;
  categories: ICategory[];
  tags: ITag[];
}

defineProps<Props>();

const selectedMonth = ref<{ month: number; year: number } | null>(null);

const handleMonthUpdate = (payload: { month: number; year: number }) => {
  const now = new Date();
  const isCurrentMonth =
    payload.month === now.getMonth() + 1 && payload.year === now.getFullYear();

  selectedMonth.value = isCurrentMonth ? null : payload;

  router.reload({
    data: { forecast_month: payload.month, forecast_year: payload.year },
    only: ['forecast', 'monthlyIncome', 'monthlyExpenses', 'dailyBalances', 'carryOver'],
  });
};
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- HEADING -->
    <Heading
      title="Dashboard"
      description="Here you can get an overview of your financial activities and manage your transactions efficiently."
      :hasActions="true"
    >
      <CreateTransactionButton />
    </Heading>

    <section class="flex flex-col gap-4">
      <!-- CALENDAR -->
      <HorizontalCalendarStrip @update:month="handleMonthUpdate" />

      <!-- CARDS -->
      <BalanceCards :availableBalance :forecast :selectedMonth="selectedMonth" />

      <!-- CHART -->
      <ChartMonthly
        :monthlyIncome
        :monthlyExpenses
        :dailyBalances
        :carryOver
        :selectedMonth
      />

      <!-- LAST TRANSACTIONS -->
      <LastTransactionsTable :latestTransactions />
    </section>

    <CreateTransactionDialog :categories="categories" :tags="tags" />
  </AppLayout>
</template>
