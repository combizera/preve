<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
  CalendarRange,
  Check,
  Flag,
  ListChecks,
  PiggyBank,
  Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import BalanceAdjustStep from '@/components/Reconciliation/BalanceAdjustStep.vue';
import BucketAdjustStep from '@/components/Reconciliation/BucketAdjustStep.vue';
import ConfirmHiatusCard from '@/components/Reconciliation/ConfirmHiatusCard.vue';
import RecurringReviewStep from '@/components/Reconciliation/RecurringReviewStep.vue';
import SummaryStep from '@/components/Reconciliation/SummaryStep.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { show } from '@/routes/reconciliation';
import type { BreadcrumbItem } from '@/types';
import type { IHiatus } from '@/types/models/hiatus';
import type { IRecurringTransaction } from '@/types/models/recurring-transaction';
import type { ISavingsBucket } from '@/types/models/savings-bucket';
import type { ITransaction } from '@/types/models/transaction';

defineProps<{
  hiatus: IHiatus | null;
  suggestedStart: string | null;
  pendingRecurring: ITransaction[];
  activeRecurring: IRecurringTransaction[];
  savingsBuckets: ISavingsBucket[];
  currentBalance: number;
}>();

const { t, locale } = useI18n();

const formatDate = (value: string): string =>
  new Date(value).toLocaleDateString(locale.value, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'UTC',
  });

const breadcrumbs: BreadcrumbItem[] = [
  { title: t('reconciliation.title'), href: show().url },
];

const currentStep = ref(1);

const advance = () => {
  currentStep.value++;
};

const steps = computed(() => [
  { icon: ListChecks, label: t('reconciliation.steps.recurring') },
  { icon: PiggyBank, label: t('reconciliation.steps.savings') },
  { icon: Wallet, label: t('reconciliation.steps.balance') },
  { icon: Flag, label: t('reconciliation.steps.summary') },
]);
</script>

<template>
  <Head :title="t('reconciliation.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto grid max-w-4xl gap-6">
      <div class="grid gap-1">
        <h1 class="text-2xl font-semibold">
          {{ t('reconciliation.heading') }}
        </h1>
        <p class="text-muted-foreground">
          {{
            hiatus
              ? t('reconciliation.subheadingWithHiatus')
              : t('reconciliation.subheading')
          }}
        </p>
      </div>

      <div v-if="hiatus" class="flex flex-wrap items-center gap-2">
        <div
          v-for="(step, index) in steps"
          :key="step.label"
          class="flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm"
          :class="
            currentStep === index + 1
              ? 'border-amber-500/40 bg-amber-500/10 text-foreground'
              : 'text-muted-foreground'
          "
        >
          <Check
            v-if="currentStep > index + 1"
            :size="14"
            class="text-emerald-600"
          />
          <component :is="step.icon" v-else :size="14" />
          <span>{{ index + 1 }}. {{ step.label }}</span>
        </div>
      </div>

      <div
        v-if="hiatus"
        class="flex items-center gap-2 text-sm text-muted-foreground"
      >
        <CalendarRange :size="14" />
        {{
          t('reconciliation.hiatusPeriod', {
            start: formatDate(hiatus.started_at),
            end: hiatus.ended_at
              ? formatDate(hiatus.ended_at)
              : t('reconciliation.today'),
          })
        }}
      </div>

      <ConfirmHiatusCard v-if="!hiatus" :suggested-start="suggestedStart" />
      <template v-else>
        <RecurringReviewStep
          v-if="currentStep === 1"
          :pending-recurring="pendingRecurring"
          :active-recurring="activeRecurring"
          @completed="advance"
        />
        <BucketAdjustStep
          v-else-if="currentStep === 2"
          :savings-buckets="savingsBuckets"
          @completed="advance"
        />
        <BalanceAdjustStep
          v-else-if="currentStep === 3"
          :current-balance="currentBalance"
          @completed="advance"
        />
        <SummaryStep v-else />

        <div v-if="currentStep < 4" class="justify-self-end">
          <Button variant="ghost" size="sm" @click="advance">
            {{ t('reconciliation.skip') }}
          </Button>
        </div>
      </template>
    </div>
  </AppLayout>
</template>
