<script setup lang="ts">
import { CreditCard, ReceiptText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { invoice } from '@/actions/App/Http/Controllers/CreditCardController';
import ActionGroup from '@/components/ActionGroup.vue';
import DeleteCreditCardDialog from '@/components/CreditCard/DeleteCreditCardDialog.vue';
import EditCreditCardDialog from '@/components/CreditCard/EditCreditCardDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { getCreditCardColorClass } from '@/lib/credit-card-colors';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { cn } from '@/lib/utils';
import { useCreditCardStore } from '@/stores/credit-card.store';
import type { ICreditCard } from '@/types/models/credit-card';

const { t } = useI18n();
const store = useCreditCardStore();

defineProps<{
  creditCards: ICreditCard[];
}>();

function usagePercent(card: ICreditCard): number {
  if (!card.credit_limit || card.credit_limit <= 0) return 0;
  const ratio = ((card.committed ?? 0) / card.credit_limit) * 100;
  return Math.max(0, Math.min(100, Math.round(ratio)));
}
</script>

<template>
  <div class="mt-6">
    <EmptyState
      v-if="creditCards.length === 0"
      :title="t('creditCards.empty.title')"
      :description="t('creditCards.empty.description')"
      :show-button="false"
    >
      <template #icon>
        <CreditCard class="size-8 text-muted-foreground" />
      </template>
    </EmptyState>

    <Table v-else>
      <TableHeader>
        <TableRow>
          <TableHead>{{ t('generic.labels.name') }}</TableHead>
          <TableHead>{{ t('creditCards.table.cycle') }}</TableHead>
          <TableHead class="w-[35%]">{{
            t('creditCards.table.usage')
          }}</TableHead>
          <TableHead class="text-right">
            {{ t('generic.labels.actions') }}
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="card in creditCards" :key="card.id">
          <TableCell class="flex items-center gap-3">
            <div
              :class="[
                getCreditCardColorClass(card.color, 'bg'),
                getCreditCardColorClass(card.color, 'border'),
                'inline-flex items-center justify-center rounded border p-1.5',
              ]"
            >
              <CreditCard
                :size="18"
                :class="getCreditCardColorClass(card.color, 'text')"
              />
            </div>
            <div>
              <p class="font-medium">{{ card.name }}</p>
              <p v-if="card.last_four" class="text-xs text-muted-foreground">
                •••• {{ card.last_four }}
              </p>
            </div>
          </TableCell>
          <TableCell class="text-sm whitespace-nowrap text-muted-foreground">
            {{
              t('creditCards.cycleSummary', {
                closing: card.closing_day,
                due: card.due_day,
              })
            }}
          </TableCell>
          <TableCell>
            <div v-if="card.credit_limit" class="space-y-1.5">
              <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-foreground">
                  {{ getCurrencySymbol() }}
                  {{ formatCentsToDisplay(card.committed ?? 0) }}
                </span>
                <span class="text-muted-foreground">
                  / {{ getCurrencySymbol() }}
                  {{ formatCentsToDisplay(card.credit_limit) }}
                </span>
              </div>
              <div
                class="relative h-1.5 w-full overflow-hidden rounded-full bg-foreground/10"
              >
                <div
                  :class="
                    cn(
                      'h-full transition-all',
                      getCreditCardColorClass(card.color, 'picker'),
                    )
                  "
                  :style="{ width: `${usagePercent(card)}%` }"
                />
              </div>
              <p class="text-xs text-muted-foreground">
                {{ usagePercent(card) }}%
              </p>
            </div>
            <div v-else class="text-sm">
              <span class="font-medium text-foreground">
                {{ getCurrencySymbol() }}
                {{ formatCentsToDisplay(card.committed ?? 0) }}
              </span>
              <span class="ml-1 text-xs text-muted-foreground">
                {{ t('creditCards.usage.committed') }}
              </span>
            </div>
          </TableCell>
          <TableCell class="text-right">
            <ActionGroup>
              <DropdownMenuItem as-child>
                <a target="_blank" :href="invoice(card.id).url">
                  <ReceiptText class="size-4" />
                  {{ t('creditCards.actions.viewInvoice') }}
                </a>
              </DropdownMenuItem>
              <EditButton @click="store.openEditModal(card)" />
              <DeleteButton @click="store.openDeleteModal(card)" />
            </ActionGroup>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <EditCreditCardDialog />
    <DeleteCreditCardDialog />
  </div>
</template>
