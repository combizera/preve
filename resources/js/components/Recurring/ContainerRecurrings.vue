<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import EmptyState from '@/components/EmptyState.vue';
import CardRecurring from '@/components/Recurring/CardRecurring.vue';
import SectionTitle from '@/components/SectionTitle.vue';
import { useRecurringStore } from '@/stores/recurring.store';
import { type ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';
import { TransactionType } from '@/types/models/transaction';

interface Props {
  recurringTransactions: IRecurringTransaction[];
  categories: ICategory[];
  tags: ITag[];
  type: TransactionType;
}

const { t } = useI18n();
const recurringStore = useRecurringStore();
const props = defineProps<Props>();

const typeLabel = computed(() =>
  props.type === 'income' ? t('models.transaction.income') : t('models.transaction.expense'),
);

const emptyStateConfig = computed(() => ({
  title: t('recurring.empty.title', { type: typeLabel.value.toLowerCase() }),
  description: t('recurring.empty.description', { type: typeLabel.value.toLowerCase() }),
  buttonText: t('recurring.empty.button', { type: typeLabel.value }),
}));
</script>

<template>
  <div class="space-y-3">
    <!-- TITLE -->
    <SectionTitle :title="typeLabel" />

    <!-- Empty State -->
    <EmptyState
      v-if="recurringTransactions.length === 0"
      :title="emptyStateConfig.title"
      :description="emptyStateConfig.description"
      :button-text="emptyStateConfig.buttonText"
      @action="recurringStore.openCreateDialog(type)"
    />

    <!-- Transactions List -->
    <div v-else class="space-y-2">
      <CardRecurring
        :recurringTransaction="recurringTransaction"
        :categories="categories"
        :tags="tags"
        v-for="recurringTransaction in recurringTransactions"
        :key="recurringTransaction.id"
      />
    </div>
  </div>
</template>
