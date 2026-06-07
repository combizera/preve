<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import TagsMultiSelect from '@/components/Tag/TagsMultiSelect.vue';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
  InputGroupText,
} from '@/components/ui/input-group';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import { getCurrencySymbol } from '@/lib/currency';
import type { ICategory } from '@/types/models/category';
import type { ICreditCard } from '@/types/models/credit-card';
import type { ITag } from '@/types/models/tag';
import type { ITransactionInput } from '@/types/models/transaction';
import { filterNumericInput } from '@/utils/numericInput';

interface Props {
  form: InertiaForm<ITransactionInput>;
  categories: ICategory[];
  tags: ITag[];
  creditCards?: ICreditCard[];
}

const props = withDefaults(defineProps<Props>(), {
  creditCards: () => [],
});

const { t } = useI18n();

const displayAmount = defineModel<string>('displayAmount', { required: true });
const currencySymbol = getCurrencySymbol();

const filteredCategories = computed(() => {
  return props.categories.filter(
    (category) => category.type === props.form.type,
  );
});

const NO_CARD = 0;

const cardModel = computed<number>({
  get: () => props.form.credit_card_id ?? NO_CARD,
  set: (value) => {
    props.form.credit_card_id = value === NO_CARD ? null : value;
    props.form.splits = props.form.credit_card_id
      ? (props.form.splits ?? 1)
      : null;
  },
});

const hasCard = computed(() => !!props.form.credit_card_id);

const showCardField = computed(
  () =>
    props.creditCards.length > 0 &&
    props.form.type === TRANSACTION_TYPE.EXPENSE,
);

watch(
  () => props.form.type,
  (type) => {
    if (type !== TRANSACTION_TYPE.EXPENSE) {
      props.form.credit_card_id = null;
      props.form.splits = null;
    }
  },
);
</script>

<template>
  <!-- Type -->
  <div class="grid gap-3">
    <Label for="type"> {{ t('models.transaction.type') }} </Label>
    <ToggleGroup v-model="form.type" class="w-full">
      <ToggleGroupItem :value="TRANSACTION_TYPE.EXPENSE" class="flex-1 gap-2">
        <ArrowUpRight :size="16" />
        {{ t('models.transaction.expense') }}
      </ToggleGroupItem>
      <ToggleGroupItem :value="TRANSACTION_TYPE.INCOME" class="flex-1 gap-2">
        <ArrowDownLeft :size="16" />
        {{ t('models.transaction.income') }}
      </ToggleGroupItem>
    </ToggleGroup>
    <InputError :message="form.errors.type" />
  </div>

  <!-- Amount & Date -->
  <div class="grid grid-cols-2 gap-4">
    <div class="grid gap-3">
      <Label for="amount"> {{ t('models.transaction.amount') }} </Label>
      <InputGroup>
        <InputGroupAddon>
          <InputGroupText>{{ currencySymbol }}</InputGroupText>
        </InputGroupAddon>
        <InputGroupInput
          id="amount"
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
      <Label for="transaction_date"> {{ t('models.transaction.date') }} </Label>
      <DatePicker
        id="transaction_date"
        v-model="form.transaction_date"
        class="[&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:dark:invert-0"
      />
      <InputError :message="form.errors.transaction_date" />
    </div>
  </div>

  <!-- Category & Tag -->
  <div class="grid grid-cols-2 gap-4">
    <div class="grid gap-3">
      <Label for="category"> {{ t('models.category.name') }} </Label>
      <Select v-model="form.category_id">
        <SelectTrigger class="w-full">
          <SelectValue
            :placeholder="t('generic.placeholders.selectCategory')"
          />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>{{ t('models.category.name') }}</SelectLabel>
            <SelectItem
              v-for="category in filteredCategories"
              :value="category.id"
              :key="category.id"
            >
              {{ category.name }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
      <InputError :message="form.errors.category_id" />
    </div>

    <div class="grid gap-3">
      <Label for="tag" class="text-muted-foreground">
        {{ t('models.tag.optional') }}
      </Label>
      <TagsMultiSelect id="tag" v-model="form.tags" :tags="tags" />
      <InputError :message="form.errors.tags" />
    </div>
  </div>

  <!-- Credit card & Installments -->
  <div
    v-if="showCardField"
    :class="hasCard ? 'grid grid-cols-2 gap-4' : 'grid gap-4'"
  >
    <div class="grid gap-3">
      <Label for="credit_card" class="text-muted-foreground">
        {{ t('transactions.fields.creditCardOptional') }}
      </Label>
      <Select v-model="cardModel">
        <SelectTrigger class="w-full">
          <SelectValue
            :placeholder="t('transactions.fields.creditCardPlaceholder')"
          />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectItem :value="NO_CARD">
              {{ t('generic.labels.none') }}
            </SelectItem>
            <SelectItem
              v-for="card in creditCards"
              :value="card.id"
              :key="card.id"
            >
              {{ card.name }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
      <InputError :message="form.errors.credit_card_id" />
    </div>

    <div v-if="hasCard" class="grid gap-3">
      <Label for="splits"> {{ t('transactions.fields.splits') }} </Label>
      <Input id="splits" type="number" min="1" max="48" v-model="form.splits" />
      <p class="text-xs text-muted-foreground">
        {{ t('transactions.fields.splitsHint') }}
      </p>
      <InputError :message="form.errors.splits" />
    </div>
  </div>

  <!-- Description -->
  <div class="grid gap-3">
    <Label for="description"> {{ t('models.transaction.description') }} </Label>
    <Input
      id="description"
      :placeholder="t('generic.placeholders.transactionDescription')"
      v-model="form.description"
    />
    <InputError :message="form.errors.description" />
  </div>

  <!-- Notes -->
  <div class="grid gap-3">
    <Label for="notes" class="text-muted-foreground">
      {{ t('generic.labels.notesOptional') }}
    </Label>
    <Input
      id="notes"
      :placeholder="t('generic.placeholders.additionalNotes')"
      v-model="form.notes"
    />
    <InputError :message="form.errors.notes" />
  </div>
</template>
