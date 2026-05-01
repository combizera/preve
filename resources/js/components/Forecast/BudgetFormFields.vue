<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
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
import type { IForecastInput } from '@/types/models/forecast';
import { filterNumericInput } from '@/utils/numericInput';

const props = defineProps<{
  form: InertiaForm<IForecastInput>;
  showApplyToDefault?: boolean;
}>();

const { t } = useI18n();

const displayAmount = computed({
  get: () => formatCentsToDisplay(props.form.amount),
  set: (value: string) => {
    props.form.amount = parseToCents(extractNumbers(value));
  },
});
</script>

<template>
  <div class="grid gap-4">
    <div class="grid gap-3">
      <Label for="budget_amount">{{ t('forecasts.form.amount') }}</Label>
      <InputGroup>
        <InputGroupAddon>
          <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
        </InputGroupAddon>
        <InputGroupInput
          id="budget_amount"
          type="text"
          inputmode="numeric"
          placeholder="0,00"
          v-model="displayAmount"
          @keydown="filterNumericInput"
        />
      </InputGroup>
      <InputError :message="form.errors.amount" />
    </div>

    <div class="grid gap-3">
      <Label for="budget_notes" class="text-muted-foreground">
        {{ t('forecasts.form.notes') }}
      </Label>
      <Input
        id="budget_notes"
        :placeholder="t('generic.placeholders.additionalNotes')"
        v-model="form.notes"
      />
      <InputError :message="form.errors.notes" />
    </div>

    <div
      v-if="showApplyToDefault"
      class="flex items-start gap-3 rounded-md border bg-muted/30 p-3"
    >
      <Checkbox
        id="budget_apply_default"
        :model-value="form.apply_to_default ?? false"
        @update:model-value="(value) => (form.apply_to_default = !!value)"
        class="mt-0.5"
      />
      <div class="grid gap-1">
        <Label
          for="budget_apply_default"
          class="cursor-pointer text-sm leading-none font-medium"
        >
          {{ t('forecasts.form.applyToDefault') }}
        </Label>
        <p class="text-xs text-muted-foreground">
          {{ t('forecasts.form.applyToDefaultHelp') }}
        </p>
      </div>
    </div>
  </div>
</template>
