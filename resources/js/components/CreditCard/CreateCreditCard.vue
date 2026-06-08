<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import CreditCardPreview from '@/components/CreditCard/CreditCardPreview.vue';
import InputError from '@/components/InputError.vue';
import QuickCreateCard from '@/components/QuickCreateCard.vue';
import { Button } from '@/components/ui/button';
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
import {
  availableCreditCardColors,
  getCreditCardColorClass,
} from '@/lib/credit-card-colors';
import {
  extractNumbers,
  formatCentsToDisplay,
  getCurrencySymbol,
  parseToCents,
} from '@/lib/currency';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { store } from '@/routes/credit-cards';
import type { ICreditCardForm } from '@/types/models/credit-card';
import { filterNumericInput } from '@/utils/numericInput';

const { t } = useI18n();

const form = useForm<ICreditCardForm>({
  name: '',
  last_four: null,
  closing_day: 1,
  due_day: 10,
  color: undefined,
  credit_limit: null,
});

const displayLimit = computed({
  get: () => formatCentsToDisplay(form.credit_limit ?? 0),
  set: (value: string) => {
    form.credit_limit = parseToCents(extractNumbers(value));
  },
});

const createCard = () => {
  form.submit(store(), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <QuickCreateCard :title="t('creditCards.newCard')" collapsible>
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
      <CreditCardPreview
        class="w-full shrink-0 lg:w-80"
        :name="form.name"
        :last-four="form.last_four"
        :color="form.color"
      />

      <form class="grid flex-1 gap-4" @submit.prevent="createCard">
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2 grid gap-2">
            <Label for="card_name">{{ t('creditCards.fields.name') }}</Label>
            <Input id="card_name" name="name" v-model="form.name" />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-2">
            <Label for="card_color">{{ t('generic.labels.color') }}</Label>
            <Select v-model="form.color">
              <SelectTrigger class="w-full">
                <SelectValue
                  :placeholder="t('generic.placeholders.selectColor')"
                >
                  <div v-if="form.color" class="flex items-center gap-2">
                    <div
                      :class="
                        cn(
                          'h-4 w-4 rounded',
                          getCreditCardColorClass(form.color, 'picker'),
                        )
                      "
                    />
                    <span>{{ capitalizeFirstLetter(form.color) }}</span>
                  </div>
                </SelectValue>
              </SelectTrigger>
              <SelectContent
                class="w-auto min-w-0 [&_[data-reka-select-viewport]]:w-auto! [&_[data-reka-select-viewport]]:min-w-0!"
              >
                <SelectGroup>
                  <SelectLabel>{{ t('generic.labels.color') }}</SelectLabel>
                  <div class="grid grid-cols-6 gap-1.5 p-1">
                    <SelectItem
                      v-for="color in availableCreditCardColors"
                      :value="color"
                      :key="color"
                      class="size-7 cursor-pointer rounded-md bg-transparent! p-0! hover:brightness-90 [&>span]:hidden"
                    >
                      <div
                        :class="
                          cn(
                            'size-7 rounded',
                            getCreditCardColorClass(color, 'picker'),
                          )
                        "
                      />
                    </SelectItem>
                  </div>
                </SelectGroup>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.color" />
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div class="grid gap-2">
            <Label for="card_last_four">
              {{ t('creditCards.fields.lastFour') }}
            </Label>
            <Input
              id="card_last_four"
              inputmode="numeric"
              maxlength="4"
              placeholder="1234"
              v-model="form.last_four"
              @keydown="filterNumericInput"
            />
            <InputError :message="form.errors.last_four" />
          </div>

          <div class="grid gap-2">
            <Label for="card_closing">
              {{ t('creditCards.fields.closingDay') }}
            </Label>
            <Input
              id="card_closing"
              type="number"
              min="1"
              max="31"
              v-model="form.closing_day"
            />
            <InputError :message="form.errors.closing_day" />
          </div>

          <div class="grid gap-2">
            <Label for="card_due">{{ t('creditCards.fields.dueDay') }}</Label>
            <Input
              id="card_due"
              type="number"
              min="1"
              max="31"
              v-model="form.due_day"
            />
            <InputError :message="form.errors.due_day" />
          </div>
        </div>

        <div class="flex items-end gap-3">
          <div class="grid flex-1 gap-2">
            <Label for="card_limit" class="text-muted-foreground">
              {{ t('creditCards.fields.creditLimitOptional') }}
            </Label>
            <InputGroup>
              <InputGroupAddon>
                <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
              </InputGroupAddon>
              <InputGroupInput
                id="card_limit"
                type="text"
                inputmode="numeric"
                placeholder="0,00"
                v-model="displayLimit"
                @keydown="filterNumericInput"
              />
            </InputGroup>
            <InputError :message="form.errors.credit_limit" />
          </div>

          <Button type="submit" :disabled="form.processing">
            {{ t('generic.actions.create') }}
          </Button>
        </div>
      </form>
    </div>
  </QuickCreateCard>
</template>
