<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { recurring as reviewRecurringRoute } from '@/routes/reconciliation';
import type { IRecurringTransaction } from '@/types/models/recurring-transaction';
import type { ITransaction } from '@/types/models/transaction';

const props = defineProps<{
  pendingRecurring: ITransaction[];
  activeRecurring: IRecurringTransaction[];
}>();

const emit = defineEmits<{
  (e: 'completed'): void;
}>();

const { t, locale } = useI18n();

const formatDate = (value?: string | null): string => {
  if (!value) return '';
  return new Date(value).toLocaleDateString(locale.value, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'UTC',
  });
};

const keptIds = ref(new Set(props.pendingRecurring.map((tx) => tx.id!)));
const deactivateIds = ref(new Set<string>());

const toggleKept = (id: string) => {
  if (keptIds.value.has(id)) {
    keptIds.value.delete(id);
  } else {
    keptIds.value.add(id);
  }
};

const toggleDeactivate = (id: string) => {
  if (deactivateIds.value.has(id)) {
    deactivateIds.value.delete(id);
  } else {
    deactivateIds.value.add(id);
  }
};

const keepAll = () => {
  keptIds.value = new Set(props.pendingRecurring.map((tx) => tx.id!));
};

const removeAll = () => {
  keptIds.value = new Set();
};

const deleteCount = computed(
  () => props.pendingRecurring.length - keptIds.value.size,
);

const form = useForm({
  delete_transaction_ids: [] as string[],
  deactivate_recurring_ids: [] as string[],
});

const firstError = computed(() => Object.values(form.errors)[0]);

const submit = () => {
  form.delete_transaction_ids = props.pendingRecurring
    .map((tx) => tx.id!)
    .filter((id) => !keptIds.value.has(id));
  form.deactivate_recurring_ids = [...deactivateIds.value];

  if (
    form.delete_transaction_ids.length === 0 &&
    form.deactivate_recurring_ids.length === 0
  ) {
    emit('completed');
    return;
  }

  form.submit(reviewRecurringRoute(), {
    preserveScroll: true,
    onSuccess: () => emit('completed'),
  });
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>{{ t('reconciliation.recurringStep.title') }}</CardTitle>
      <CardDescription>
        {{ t('reconciliation.recurringStep.description') }}
      </CardDescription>
    </CardHeader>
    <CardContent class="grid gap-6">
      <div v-if="pendingRecurring.length > 0" class="grid gap-2">
        <div class="flex justify-end gap-2">
          <Button size="sm" variant="outline" @click="keepAll">
            {{ t('reconciliation.recurringStep.keepAll') }}
          </Button>
          <Button size="sm" variant="outline" @click="removeAll">
            {{ t('reconciliation.recurringStep.removeAll') }}
          </Button>
        </div>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-12">
                {{ t('reconciliation.recurringStep.keep') }}
              </TableHead>
              <TableHead>{{ t('models.transaction.date') }}</TableHead>
              <TableHead>{{ t('models.transaction.description') }}</TableHead>
              <TableHead>{{ t('models.category.name') }}</TableHead>
              <TableHead class="text-right">
                {{ t('models.transaction.amount') }}
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="tx in pendingRecurring"
              :key="tx.id"
              :class="{ 'opacity-50': !keptIds.has(tx.id!) }"
            >
              <TableCell>
                <Checkbox
                  :model-value="keptIds.has(tx.id!)"
                  @update:model-value="toggleKept(tx.id!)"
                />
              </TableCell>
              <TableCell>
                {{ formatDate(tx.purchase_date ?? tx.transaction_date) }}
              </TableCell>
              <TableCell>{{ tx.description }}</TableCell>
              <TableCell>{{ tx.category?.name }}</TableCell>
              <TableCell
                class="text-right font-mono"
                :class="
                  tx.type === 'income' ? 'text-emerald-600' : 'text-red-500'
                "
              >
                {{ tx.type === 'income' ? '+' : '-' }}
                {{ getCurrencySymbol() }}
                {{ formatCentsToDisplay(tx.amount) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <p v-else class="text-sm text-muted-foreground">
        {{ t('reconciliation.recurringStep.empty') }}
      </p>

      <template v-if="activeRecurring.length > 0">
        <Separator />
        <div class="grid gap-3">
          <div>
            <h3 class="text-sm font-medium">
              {{ t('reconciliation.recurringStep.deactivateTitle') }}
            </h3>
            <p class="text-sm text-muted-foreground">
              {{ t('reconciliation.recurringStep.deactivateDescription') }}
            </p>
          </div>
          <label
            v-for="recurring in activeRecurring"
            :key="recurring.id"
            class="flex cursor-pointer items-center gap-3 rounded-lg border p-3"
          >
            <Checkbox
              :model-value="deactivateIds.has(recurring.id!)"
              @update:model-value="toggleDeactivate(recurring.id!)"
            />
            <span class="flex-1 text-sm">
              {{ recurring.description }}
              <span class="text-muted-foreground">
                · {{ recurring.category?.name }}
              </span>
            </span>
            <span class="font-mono text-sm text-muted-foreground">
              {{ getCurrencySymbol() }}
              {{ formatCentsToDisplay(recurring.amount) }}
            </span>
          </label>
        </div>
      </template>
    </CardContent>
    <CardFooter class="flex items-center justify-between gap-4">
      <div class="grid gap-1">
        <p class="text-sm text-muted-foreground">
          {{
            t('reconciliation.recurringStep.summary', {
              remove: deleteCount,
              deactivate: deactivateIds.size,
            })
          }}
        </p>
        <InputError :message="firstError" />
      </div>
      <Button type="button" @click="submit" :disabled="form.processing">
        {{ t('reconciliation.recurringStep.submit') }}
      </Button>
    </CardFooter>
  </Card>
</template>
