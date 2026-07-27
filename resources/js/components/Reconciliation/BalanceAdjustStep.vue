<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { MoveRight } from 'lucide-vue-next';
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
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
  InputGroupText,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';
import {
  extractNumbers,
  formatCentsToDisplay,
  getCurrencySymbol,
  parseToCents,
} from '@/lib/currency';
import { balance as balanceRoute } from '@/routes/reconciliation';
import { filterNumericInput } from '@/utils/numericInput';

const props = defineProps<{
  currentBalance: number;
}>();

const emit = defineEmits<{
  (e: 'completed'): void;
}>();

const { t } = useI18n();

const rawAmount = ref(String(Math.max(props.currentBalance, 0)));

const displayAmount = computed({
  get: () => formatCentsToDisplay(rawAmount.value),
  set: (value: string) => {
    rawAmount.value = extractNumbers(value);
  },
});

const difference = computed(
  () => parseToCents(rawAmount.value) - props.currentBalance,
);

const form = useForm({ real_balance: 0 });

const submit = () => {
  if (difference.value === 0) {
    emit('completed');
    return;
  }

  form.real_balance = parseToCents(rawAmount.value);

  form.submit(balanceRoute(), {
    preserveScroll: true,
    onSuccess: () => emit('completed'),
  });
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>{{ t('reconciliation.balanceStep.title') }}</CardTitle>
      <CardDescription>
        {{ t('reconciliation.balanceStep.description') }}
      </CardDescription>
    </CardHeader>
    <CardContent class="grid gap-4">
      <div class="flex flex-wrap items-end gap-4">
        <div class="grid gap-1">
          <span class="text-xs text-muted-foreground">
            {{ t('reconciliation.balanceStep.currentLabel') }}
          </span>
          <span class="font-mono text-2xl font-semibold">
            {{ getCurrencySymbol() }}
            {{ formatCentsToDisplay(currentBalance) }}
          </span>
        </div>
        <MoveRight class="mb-1.5 text-muted-foreground" :size="20" />
        <div class="grid gap-1">
          <Label for="real_balance" class="text-xs">
            {{ t('reconciliation.balanceStep.realLabel') }}
          </Label>
          <InputGroup class="w-48">
            <InputGroupAddon>
              <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
            </InputGroupAddon>
            <InputGroupInput
              id="real_balance"
              type="text"
              inputmode="numeric"
              v-model="displayAmount"
              @keydown="filterNumericInput"
              class="text-right font-mono text-lg"
            />
          </InputGroup>
        </div>
      </div>

      <p
        class="text-sm"
        :class="difference === 0 ? 'text-muted-foreground' : ''"
      >
        <template v-if="difference === 0">
          {{ t('reconciliation.balanceStep.noAdjust') }}
        </template>
        <template v-else>
          {{
            t('reconciliation.balanceStep.willAdjust', {
              amount: `${difference > 0 ? '+' : '-'} ${getCurrencySymbol()} ${formatCentsToDisplay(Math.abs(difference))}`,
            })
          }}
        </template>
      </p>
      <InputError :message="form.errors.real_balance" />
    </CardContent>
    <CardFooter class="justify-end">
      <Button type="button" @click="submit" :disabled="form.processing">
        {{ t('reconciliation.balanceStep.submit') }}
      </Button>
    </CardFooter>
  </Card>
</template>
