<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

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
import { availableColors, getColorClass } from '@/lib/accent-colors';
import {
  extractNumbers,
  formatCentsToDisplay,
  getCurrencySymbol,
  parseToCents,
} from '@/lib/currency';
import {
  availableSavingsBucketIcons,
  getSavingsBucketIconComponent,
} from '@/lib/savings-bucket-icons';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { store } from '@/routes/savings';
import type { ISavingsBucketForm } from '@/types/models/savings-bucket';
import { filterNumericInput } from '@/utils/numericInput';

const { t } = useI18n();

const form = useForm<ISavingsBucketForm>({
  name: '',
  target_amount: 0,
  initial_amount: 0,
  color: undefined,
  icon: undefined,
});

const displayTarget = computed({
  get: () => formatCentsToDisplay(form.target_amount),
  set: (value: string) => {
    form.target_amount = parseToCents(extractNumbers(value));
  },
});

const displayInitial = computed({
  get: () => formatCentsToDisplay(form.initial_amount ?? 0),
  set: (value: string) => {
    form.initial_amount = parseToCents(extractNumbers(value));
  },
});

const createBucket = () => {
  form.submit(store(), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <QuickCreateCard :title="t('savings.newBucket')">
    <form
      class="flex w-full flex-col items-start gap-3 lg:flex-row lg:flex-nowrap"
      @submit.prevent="createBucket"
    >
      <div class="flex w-full flex-col gap-2 lg:min-w-0 lg:flex-[2]">
        <Label for="savings_name">{{ t('savings.fields.name') }}</Label>
        <Input
          id="savings_name"
          name="name"
          :placeholder="t('generic.placeholders.savingsBucketName')"
          v-model="form.name"
        />
        <InputError :message="form.errors.name" />
      </div>

      <div class="flex w-full flex-col gap-2 lg:min-w-0 lg:flex-1">
        <Label for="savings_target">{{ t('savings.fields.target') }}</Label>
        <InputGroup>
          <InputGroupAddon>
            <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
          </InputGroupAddon>
          <InputGroupInput
            id="savings_target"
            type="text"
            inputmode="numeric"
            placeholder="0,00"
            v-model="displayTarget"
            @keydown="filterNumericInput"
          />
        </InputGroup>
        <InputError :message="form.errors.target_amount" />
      </div>

      <div class="flex w-full flex-col gap-2 lg:min-w-0 lg:flex-1">
        <Label for="savings_initial" class="text-muted-foreground">
          {{ t('savings.fields.initial') }}
        </Label>
        <InputGroup>
          <InputGroupAddon>
            <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
          </InputGroupAddon>
          <InputGroupInput
            id="savings_initial"
            type="text"
            inputmode="numeric"
            placeholder="0,00"
            v-model="displayInitial"
            @keydown="filterNumericInput"
          />
        </InputGroup>
        <p class="text-xs text-muted-foreground">
          {{ t('savings.fields.initialHint') }}
        </p>
        <InputError :message="form.errors.initial_amount" />
      </div>

      <div class="flex w-full flex-col gap-2 lg:w-36 lg:flex-none">
        <Label for="savings_color">{{ t('generic.labels.color') }}</Label>
        <Select v-model="form.color">
          <SelectTrigger class="w-full">
            <SelectValue :placeholder="t('generic.placeholders.selectColor')">
              <div v-if="form.color" class="flex items-center gap-2">
                <div
                  :class="
                    cn('h-4 w-4 rounded', getColorClass(form.color, 'picker'))
                  "
                />
                <span>{{ capitalizeFirstLetter(form.color) }}</span>
              </div>
            </SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>{{ t('generic.labels.color') }}</SelectLabel>
              <div class="grid grid-cols-4 gap-1">
                <SelectItem
                  v-for="color in availableColors"
                  :value="color"
                  :key="color"
                  class="flex aspect-square w-full! rounded-md bg-transparent! p-0! pl-0.5! hover:brightness-90 [&>span]:hidden"
                >
                  <div
                    :class="
                      cn(
                        'aspect-square h-full min-h-7.5 w-full rounded',
                        getColorClass(color, 'picker'),
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

      <div class="flex w-full flex-col gap-2 lg:w-36 lg:flex-none">
        <Label for="savings_icon">{{ t('generic.labels.icon') }}</Label>
        <Select v-model="form.icon">
          <SelectTrigger :class="cn('w-full', form.icon && 'pl-1')">
            <SelectValue :placeholder="t('generic.placeholders.selectIcon')">
              <div
                v-if="form.icon"
                :class="
                  cn(
                    'flex size-7 items-center justify-center rounded border p-1',
                    form.color && getColorClass(form.color, 'bg'),
                    form.color && getColorClass(form.color, 'border'),
                  )
                "
              >
                <component
                  :is="getSavingsBucketIconComponent(form.icon)"
                  :class="
                    cn(
                      'size-4',
                      form.color && getColorClass(form.color, 'text'),
                    )
                  "
                />
              </div>
            </SelectValue>
          </SelectTrigger>
          <SelectContent class="min-w-37.5">
            <SelectGroup>
              <SelectLabel>{{ t('generic.labels.icon') }}</SelectLabel>
              <div class="grid grid-cols-4 gap-1">
                <SelectItem
                  v-for="icon in availableSavingsBucketIcons"
                  :value="icon"
                  :key="icon"
                  class="flex items-center justify-center gap-2 p-2 [&>span]:hidden"
                >
                  <component
                    :is="getSavingsBucketIconComponent(icon)"
                    class="size-5 text-foreground"
                  />
                </SelectItem>
              </div>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.icon" />
      </div>

      <div
        class="flex w-full justify-end lg:w-auto lg:shrink-0 lg:items-start lg:pt-5.5"
      >
        <Button type="submit" :disabled="form.processing" class="h-9">
          {{ t('generic.actions.create') }}
        </Button>
      </div>
    </form>
  </QuickCreateCard>
</template>
