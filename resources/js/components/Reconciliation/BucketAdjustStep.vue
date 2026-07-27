<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
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
import { buckets as bucketsRoute } from '@/routes/reconciliation';
import type { ISavingsBucket } from '@/types/models/savings-bucket';
import { filterNumericInput } from '@/utils/numericInput';

const props = defineProps<{
  savingsBuckets: ISavingsBucket[];
}>();

const emit = defineEmits<{
  (e: 'completed'): void;
}>();

const { t } = useI18n();

const rawAmounts = reactive<Record<number, string>>(
  Object.fromEntries(
    props.savingsBuckets.map((bucket) => [
      bucket.id,
      String(bucket.current_amount),
    ]),
  ),
);

const diffFor = (bucket: ISavingsBucket): number =>
  parseToCents(rawAmounts[bucket.id]) - bucket.current_amount;

const adjustmentCount = computed(
  () => props.savingsBuckets.filter((bucket) => diffFor(bucket) !== 0).length,
);

const form = useForm({
  buckets: [] as { id: number; real_amount: number }[],
});

const firstError = computed(() => Object.values(form.errors)[0]);

const submit = () => {
  if (props.savingsBuckets.length === 0 || adjustmentCount.value === 0) {
    emit('completed');
    return;
  }

  form.buckets = props.savingsBuckets.map((bucket) => ({
    id: bucket.id,
    real_amount: parseToCents(rawAmounts[bucket.id]),
  }));

  form.submit(bucketsRoute(), {
    preserveScroll: true,
    onSuccess: () => emit('completed'),
  });
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>{{ t('reconciliation.bucketStep.title') }}</CardTitle>
      <CardDescription>
        {{ t('reconciliation.bucketStep.description') }}
      </CardDescription>
    </CardHeader>
    <CardContent class="grid gap-4">
      <p
        v-if="savingsBuckets.length === 0"
        class="text-sm text-muted-foreground"
      >
        {{ t('reconciliation.bucketStep.empty') }}
      </p>
      <div
        v-for="bucket in savingsBuckets"
        :key="bucket.id"
        class="grid grid-cols-1 items-center gap-3 rounded-lg border p-3 sm:grid-cols-[1fr_auto_auto]"
      >
        <div class="grid gap-0.5">
          <span class="text-sm font-medium">{{ bucket.name }}</span>
          <span class="text-sm text-muted-foreground">
            {{ t('reconciliation.bucketStep.current') }}:
            {{ getCurrencySymbol() }}
            {{ formatCentsToDisplay(bucket.current_amount) }}
          </span>
        </div>
        <div class="grid gap-1">
          <Label :for="`bucket-${bucket.id}`" class="text-xs">
            {{ t('reconciliation.bucketStep.real') }}
          </Label>
          <InputGroup class="w-40">
            <InputGroupAddon>
              <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
            </InputGroupAddon>
            <InputGroupInput
              :id="`bucket-${bucket.id}`"
              type="text"
              inputmode="numeric"
              :model-value="formatCentsToDisplay(rawAmounts[bucket.id])"
              @update:model-value="
                (value) =>
                  (rawAmounts[bucket.id] = extractNumbers(String(value)))
              "
              @keydown="filterNumericInput"
              class="text-right font-mono"
            />
          </InputGroup>
        </div>
        <span
          class="w-28 text-right font-mono text-sm"
          :class="
            diffFor(bucket) === 0
              ? 'text-muted-foreground'
              : diffFor(bucket) > 0
                ? 'text-emerald-600'
                : 'text-red-500'
          "
        >
          {{ diffFor(bucket) > 0 ? '+' : diffFor(bucket) < 0 ? '-' : '' }}
          {{
            diffFor(bucket) === 0
              ? '—'
              : formatCentsToDisplay(Math.abs(diffFor(bucket)))
          }}
        </span>
      </div>
    </CardContent>
    <CardFooter class="flex items-center justify-between gap-4">
      <div class="grid gap-1">
        <p class="text-sm text-muted-foreground">
          {{
            t('reconciliation.bucketStep.summary', { count: adjustmentCount })
          }}
        </p>
        <InputError :message="firstError" />
      </div>
      <Button type="button" @click="submit" :disabled="form.processing">
        {{ t('reconciliation.bucketStep.submit') }}
      </Button>
    </CardFooter>
  </Card>
</template>
