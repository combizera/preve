<script setup lang="ts">
import { CreditCard, ReceiptText, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import DashboardCard from '@/components/Dashboard/DashboardCard.vue';
import { formatCentsToDisplay } from '@/lib/currency';
import type { ICreditCardSummary } from '@/types/models/credit-card';

interface Props {
  summary: ICreditCardSummary;
}

const props = defineProps<Props>();

const { t } = useI18n();

const hasLimit = computed(() => props.summary.limit > 0);
</script>

<template>
  <div
    class="grid grid-cols-1 gap-4 sm:grid-cols-2"
    :class="{ 'lg:grid-cols-3': hasLimit }"
  >
    <DashboardCard
      :title="t('creditCards.summary.committed')"
      :description="t('creditCards.summary.committedHint')"
      :amount="formatCentsToDisplay(summary.committed)"
      variant="neutral"
    >
      <template #icon>
        <CreditCard :size="16" />
      </template>
    </DashboardCard>

    <DashboardCard
      v-if="hasLimit"
      :title="t('creditCards.summary.available')"
      :description="t('creditCards.summary.availableHint')"
      :amount="formatCentsToDisplay(summary.available)"
      :variant="summary.available > 0 ? 'positive' : 'destructive'"
    >
      <template #icon>
        <Wallet :size="16" />
      </template>
    </DashboardCard>

    <DashboardCard
      :title="t('creditCards.summary.invoice')"
      :description="t('creditCards.summary.invoiceHint')"
      :amount="formatCentsToDisplay(summary.invoice)"
      variant="neutral"
    >
      <template #icon>
        <ReceiptText :size="16" />
      </template>
    </DashboardCard>
  </div>
</template>
