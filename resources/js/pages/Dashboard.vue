<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import BalanceCards from '@/components/Dashboard/BalanceCards.vue';
import HorizontalCalendarStrip from '@/components/Dashboard/HorizontalCalendarStrip.vue';
import LastTransactionsTable from '@/components/Dashboard/LastTransactionsTable.vue';
import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';

import { type BreadcrumbItem } from '@/types';
import { type ITransaction } from '@/types/models/transaction';

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
}

defineProps<Props>();

const selectedMonth = ref<{ month: number; year: number } | null>(null);

const handleMonthUpdate = (payload: { month: number; year: number }) => {
  const now = new Date();
  const isCurrentMonth = payload.month === now.getMonth() + 1 && payload.year === now.getFullYear();

  selectedMonth.value = isCurrentMonth ? null : payload;

  router.reload({
    data: { forecast_month: payload.month, forecast_year: payload.year },
    only: ['forecast'],
  });
};
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="page-container">

      <!-- HEADING -->
      <Heading
        title="Dashboard"
        description="Here you can get an overview of your financial activities and manage your transactions efficiently."
        :hasActions="true"
      >
        <!-- TODO: fix issue #41 -->
        <Button type="button"> Create </Button>
      </Heading>

      <!-- CALENDAR -->
      <HorizontalCalendarStrip @update:month="handleMonthUpdate" />

      <!-- CARDS -->
      <BalanceCards :availableBalance :forecast :selectedMonth="selectedMonth" />

      <!-- PLACEHOLDER -->
      <div
        class="relative min-h-100 flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
      >
        <PlaceholderPattern />
      </div>

      <!-- LAST TRANSACTIONS -->
      <LastTransactionsTable :latestTransactions />
    </div>
  </AppLayout>
</template>
