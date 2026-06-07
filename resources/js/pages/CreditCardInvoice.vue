<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Share2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

import {
  invoice,
  shareInvoice,
} from '@/actions/App/Http/Controllers/CreditCardController';
import CreditCardPreview from '@/components/CreditCard/CreditCardPreview.vue';
import ToastProvider from '@/components/ToastProvider.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { MONTH_KEYS } from '@/lib/calendar';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import type { ICreditCard } from '@/types/models/credit-card';
import type { ITransaction } from '@/types/models/transaction';
import { formatTransactionDate } from '@/utils/formatDate';

interface Props {
  card: ICreditCard;
  year: number;
  month: number;
  transactions: ITransaction[];
  total: number;
  shared: boolean;
}

const props = defineProps<Props>();

const { t } = useI18n();

const monthString = (year: number, month: number) =>
  `${year}-${String(month).padStart(2, '0')}`;

const currentLabel = computed(() => {
  const name = t(`dashboard.calendar.months.${MONTH_KEYS[props.month - 1]}`);
  return `${name} ${props.year}`;
});

const adjacent = (delta: number) => {
  const date = new Date(props.year, props.month - 1 + delta, 1);
  return monthString(date.getFullYear(), date.getMonth() + 1);
};

const prevHref = computed(
  () => invoice(props.card.id, { query: { month: adjacent(-1) } }).url,
);
const nextHref = computed(
  () => invoice(props.card.id, { query: { month: adjacent(1) } }).url,
);

const shareLink = () => {
  router.post(
    shareInvoice(props.card.id).url,
    { month: monthString(props.year, props.month) },
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: (visit) => {
        const url = visit.props.creditCardInvoiceShareUrl as string | undefined;
        if (url) {
          navigator.clipboard.writeText(url);
          toast.success(t('creditCards.invoice.shareSuccess'));
        }
      },
      onError: () => toast.error(t('creditCards.invoice.shareError')),
    },
  );
};

const amountClass = (transaction: ITransaction) =>
  transaction.type === 'income'
    ? 'text-positive'
    : "text-foreground/80 before:content-['-']";
</script>

<template>
  <Head :title="`${t('creditCards.invoice.title')} · ${card.name}`" />

  <ToastProvider v-if="!shared" />

  <div class="mx-auto w-full max-w-xl px-4 py-10">
    <CreditCardPreview
      class="mx-auto mb-6 w-full max-w-sm"
      :name="card.name"
      :last-four="card.last_four"
      :color="card.color"
    />

    <Card class="gap-0">
      <CardHeader
        class="flex flex-row items-center justify-between gap-4 space-y-0"
      >
        <div>
          <p class="text-sm text-muted-foreground">
            {{ t('creditCards.invoice.title') }}
          </p>
          <p class="text-lg font-medium">{{ currentLabel }}</p>
        </div>

        <div v-if="!shared" class="flex items-center gap-1">
          <Button as-child variant="ghost" size="icon" class="size-8">
            <Link :href="prevHref" preserve-scroll>
              <ChevronLeft class="size-4" />
            </Link>
          </Button>
          <Button as-child variant="ghost" size="icon" class="size-8">
            <Link :href="nextHref" preserve-scroll>
              <ChevronRight class="size-4" />
            </Link>
          </Button>
        </div>
      </CardHeader>

      <CardContent class="space-y-4">
        <p
          v-if="transactions.length === 0"
          class="py-10 text-center text-sm text-muted-foreground"
        >
          {{ t('creditCards.invoice.empty') }}
        </p>

        <ul v-else class="divide-y divide-border">
          <li
            v-for="transaction in transactions"
            :key="transaction.id"
            class="flex items-center justify-between gap-4 py-3"
          >
            <div class="min-w-0 space-y-0.5">
              <p class="truncate text-sm font-medium text-foreground">
                {{ transaction.description }}
                <span
                  v-if="transaction.split_total"
                  class="text-muted-foreground"
                >
                  ·
                  {{
                    t('creditCards.invoice.installment', {
                      n: transaction.split_number,
                      total: transaction.split_total,
                    })
                  }}
                </span>
              </p>
              <p class="truncate text-xs text-muted-foreground">
                <template v-if="transaction.category">
                  {{ transaction.category.name }} ·
                </template>
                {{
                  t('creditCards.invoice.purchasedOn', {
                    date: formatTransactionDate(
                      transaction.purchase_date ?? transaction.transaction_date,
                    ),
                  })
                }}
              </p>
            </div>
            <span
              class="shrink-0 font-mono text-sm font-medium"
              :class="amountClass(transaction)"
            >
              {{ getCurrencySymbol() }}
              {{ formatCentsToDisplay(transaction.amount) }}
            </span>
          </li>
        </ul>

        <div
          class="flex items-center justify-between border-t border-border pt-4"
        >
          <span class="text-sm font-medium text-muted-foreground">
            {{ t('creditCards.invoice.total') }}
          </span>
          <span class="font-mono text-lg font-bold text-foreground">
            {{ getCurrencySymbol() }} {{ formatCentsToDisplay(total) }}
          </span>
        </div>

        <Button
          v-if="!shared"
          class="w-full"
          variant="outline"
          @click="shareLink"
        >
          <Share2 class="size-4" />
          {{ t('creditCards.invoice.share') }}
        </Button>
      </CardContent>
    </Card>
  </div>
</template>
