<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CreditCard } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { getCreditCardColorClass } from '@/lib/credit-card-colors';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import creditCardRoutes from '@/routes/credit-cards';
import type { ICreditCard } from '@/types/models/credit-card';

interface Props {
  creditCards: ICreditCard[];
}

defineProps<Props>();

const { t } = useI18n();
</script>

<template>
  <EmptyState
    v-if="creditCards.length === 0"
    :title="t('dashboard.creditCards.empty.title')"
    :description="t('dashboard.creditCards.empty.description')"
    :button-text="t('dashboard.creditCards.empty.button')"
    @action="router.visit(creditCardRoutes.index.url())"
  >
    <template #icon>
      <CreditCard class="size-8 text-muted-foreground" />
    </template>
  </EmptyState>

  <Table v-else>
    <TableHeader>
      <TableRow>
        <TableHead>{{ t('dashboard.creditCards.title') }}</TableHead>
        <TableHead class="text-right">
          {{ t('dashboard.creditCards.invoice') }}
        </TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>
      <TableRow v-for="card in creditCards" :key="card.id">
        <TableCell>
          <div class="flex items-center gap-3">
            <div
              :class="[
                getCreditCardColorClass(card.color, 'bg'),
                getCreditCardColorClass(card.color, 'border'),
                'inline-flex items-center justify-center rounded border p-1.5',
              ]"
            >
              <CreditCard
                :size="16"
                :class="getCreditCardColorClass(card.color, 'text')"
              />
            </div>
            <div class="space-y-0.5">
              <p class="text-sm font-medium text-foreground">
                {{ card.name }}
                <span v-if="card.last_four" class="text-muted-foreground">
                  ···· {{ card.last_four }}
                </span>
              </p>
              <p class="text-xs text-muted-foreground">
                {{
                  t('dashboard.creditCards.cycle', {
                    closing: card.closing_day,
                    due: card.due_day,
                  })
                }}
              </p>
            </div>
          </div>
        </TableCell>

        <TableCell class="text-right">
          <p class="text-sm font-medium text-foreground/70">
            {{ getCurrencySymbol() }}
            {{ formatCentsToDisplay(card.current_invoice ?? 0) }}
          </p>
        </TableCell>
      </TableRow>
    </TableBody>

    <TableFooter>
      <TableRow>
        <TableCell colspan="2">
          <div class="flex items-center justify-center">
            <Button
              variant="link"
              size="sm"
              class="text-muted-foreground hover:bg-transparent hover:underline"
              as-child
            >
              <Link :href="creditCardRoutes.index.url()">
                {{ t('generic.actions.viewAll') }}
              </Link>
            </Button>
          </div>
        </TableCell>
      </TableRow>
    </TableFooter>
  </Table>
</template>
