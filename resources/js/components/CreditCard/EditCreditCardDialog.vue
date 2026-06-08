<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import CreditCardPreview from '@/components/CreditCard/CreditCardPreview.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
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
import type { CreditCardColor } from '@/lib/credit-card-colors';
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
import { update } from '@/routes/credit-cards';
import { useCreditCardStore } from '@/stores/credit-card.store';
import type { ICreditCardForm } from '@/types/models/credit-card';
import { filterNumericInput } from '@/utils/numericInput';

const { t } = useI18n();
const store = useCreditCardStore();

const form = useForm<ICreditCardForm>({
  name: '',
  last_four: null,
  closing_day: null,
  due_day: null,
  color: undefined,
  credit_limit: null,
});

watch(
  () => store.creditCard,
  (card) => {
    if (card) {
      form.name = card.name;
      form.last_four = card.last_four;
      form.closing_day = card.closing_day;
      form.due_day = card.due_day;
      form.color = card.color as CreditCardColor;
      form.credit_limit = card.credit_limit;
      form.clearErrors();
    } else {
      form.reset();
    }
  },
);

const displayLimit = computed({
  get: () => formatCentsToDisplay(form.credit_limit ?? 0),
  set: (value: string) => {
    form.credit_limit = parseToCents(extractNumbers(value));
  },
});

const description = computed(() =>
  store.creditCard
    ? t('creditCards.edit.description', { name: store.creditCard.name })
    : '',
);

function save() {
  if (!store.creditCard) return;

  form.submit(update(store.creditCard.id), {
    preserveScroll: true,
    onSuccess: () => store.closeEditModal(),
  });
}

function handleOpenChange(open: boolean) {
  if (!open) {
    store.closeEditModal();
  }
}
</script>

<template>
  <Dialog v-model:open="store.showEditDialog" @update:open="handleOpenChange">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ t('creditCards.edit.title') }}</DialogTitle>
        <DialogDescription>{{ description }}</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4">
        <CreditCardPreview
          class="mx-auto w-full max-w-xs"
          :name="form.name"
          :last-four="form.last_four"
          :color="form.color"
        />

        <div class="grid gap-3">
          <Label for="edit_card_name">{{ t('creditCards.fields.name') }}</Label>
          <Input id="edit_card_name" v-model="form.name" />
          <InputError :message="form.errors.name" />
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div class="grid gap-3">
            <Label for="edit_card_last_four">
              {{ t('creditCards.fields.lastFour') }}
            </Label>
            <Input
              id="edit_card_last_four"
              inputmode="numeric"
              maxlength="4"
              v-model="form.last_four"
              @keydown="filterNumericInput"
            />
            <InputError :message="form.errors.last_four" />
          </div>

          <div class="grid gap-3">
            <Label for="edit_card_closing">
              {{ t('creditCards.fields.closingDay') }}
            </Label>
            <Input
              id="edit_card_closing"
              type="number"
              min="1"
              max="31"
              v-model="form.closing_day"
            />
            <InputError :message="form.errors.closing_day" />
          </div>

          <div class="grid gap-3">
            <Label for="edit_card_due">
              {{ t('creditCards.fields.dueDay') }}
            </Label>
            <Input
              id="edit_card_due"
              type="number"
              min="1"
              max="31"
              v-model="form.due_day"
            />
            <InputError :message="form.errors.due_day" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="grid gap-3">
            <Label for="edit_card_limit">
              {{ t('creditCards.fields.creditLimitOptional') }}
            </Label>
            <InputGroup>
              <InputGroupAddon>
                <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
              </InputGroupAddon>
              <InputGroupInput
                id="edit_card_limit"
                type="text"
                inputmode="numeric"
                placeholder="0,00"
                v-model="displayLimit"
                @keydown="filterNumericInput"
              />
            </InputGroup>
            <InputError :message="form.errors.credit_limit" />
          </div>

          <div class="grid gap-3">
            <Label for="edit_card_color">{{ t('generic.labels.color') }}</Label>
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
      </div>

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
        </DialogClose>
        <Button type="button" :disabled="form.processing" @click="save">
          {{ t('generic.actions.saveChanges') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
