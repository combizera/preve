<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

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

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
]);

interface Props {
  latestTransactions: ITransaction[];
  availableBalance: number;
  forecast: number;
  monthlyIncome: number;
  monthlyExpenses: number;
  dailyBalances: IDailyBalance[];
  dailyForecastedSpend: number;
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
    only: ['forecast', 'monthlyIncome', 'monthlyExpenses', 'dailyBalances', 'dailyForecastedSpend', 'carryOver'],
  });
};
</script>

<template>
  <Head :title="t('dashboard.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- HEADING -->
    <Heading
      :title="t('dashboard.title')"
      :description="t('dashboard.description')"
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
        :dailyForecastedSpend
        :carryOver
        :selectedMonth
      />

      <!-- LAST TRANSACTIONS -->
      <LastTransactionsTable :latestTransactions />
    </section>

    <CreateTransactionDialog :categories="categories" :tags="tags" />
  </AppLayout>
</template>
