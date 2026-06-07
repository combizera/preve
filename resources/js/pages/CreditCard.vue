<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import ContainerCreditCard from '@/components/CreditCard/ContainerCreditCard.vue';
import CreateCreditCard from '@/components/CreditCard/CreateCreditCard.vue';
import CreditCardSummary from '@/components/CreditCard/CreditCardSummary.vue';
import UpcomingInvoicesChart from '@/components/CreditCard/UpcomingInvoicesChart.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import creditCardRoutes from '@/routes/credit-cards';
import type { BreadcrumbItem } from '@/types';
import type {
  ICreditCard,
  ICreditCardSummary,
  IUpcomingInvoiceMonth,
} from '@/types/models/credit-card';

const props = defineProps<{
  creditCards: ICreditCard[];
  summary: ICreditCardSummary;
  upcomingInvoices: IUpcomingInvoiceMonth[];
}>();

const { t } = useI18n();

const hasCards = computed(() => props.creditCards.length > 0);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
  {
    title: t('creditCards.title'),
    href: creditCardRoutes.index().url,
  },
]);
</script>

<template>
  <Head :title="t('creditCards.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <Heading
      :title="t('creditCards.title')"
      :description="t('creditCards.description')"
    />

    <CreditCardSummary v-if="hasCards" :summary="summary" class="mb-4" />

    <CreateCreditCard />

    <ContainerCreditCard :credit-cards="creditCards" />

    <div v-if="hasCards" class="mt-6">
      <UpcomingInvoicesChart
        :credit-cards="creditCards"
        :upcoming-invoices="upcomingInvoices"
      />
    </div>
  </AppLayout>
</template>
