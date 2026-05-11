<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

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
import {
  AccentColor,
  availableColors,
  getColorClass,
} from '@/lib/accent-colors';
import {
  extractNumbers,
  formatCentsToDisplay,
  getCurrencySymbol,
  parseToCents,
} from '@/lib/currency';
import {
  SavingsBucketIcon,
  availableSavingsBucketIcons,
  getSavingsBucketIconComponent,
} from '@/lib/savings-bucket-icons';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { update } from '@/routes/savings';
import { useSavingsBucketStore } from '@/stores/savings-bucket.store';
import type { ISavingsBucketForm } from '@/types/models/savings-bucket';
import { filterNumericInput } from '@/utils/numericInput';

const { t } = useI18n();
const store = useSavingsBucketStore();

const form = useForm<ISavingsBucketForm>({
  name: '',
  target_amount: 0,
  color: undefined,
  icon: undefined,
});

watch(
  () => store.bucket,
  (bucket) => {
    if (bucket) {
      form.name = bucket.name;
      form.target_amount = bucket.target_amount;
      form.color = bucket.color as AccentColor;
      form.icon = bucket.icon as SavingsBucketIcon;
      form.clearErrors();
    } else {
      form.reset();
    }
  },
);

const displayTarget = computed({
  get: () => formatCentsToDisplay(form.target_amount),
  set: (value: string) => {
    form.target_amount = parseToCents(extractNumbers(value));
  },
});

const description = computed(() =>
  store.bucket
    ? t('savings.edit.description', { name: store.bucket.name })
    : '',
);

function save() {
  if (!store.bucket) return;

  form.submit(update(store.bucket.id), {
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
        <DialogTitle>{{ t('savings.edit.title') }}</DialogTitle>
        <DialogDescription>{{ description }}</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4">
        <div class="grid gap-3">
          <Label for="edit_savings_name">{{ t('savings.fields.name') }}</Label>
          <Input
            id="edit_savings_name"
            v-model="form.name"
            :placeholder="t('generic.placeholders.savingsBucketName')"
          />
          <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-3">
          <Label for="edit_savings_target">{{
            t('savings.fields.target')
          }}</Label>
          <InputGroup>
            <InputGroupAddon>
              <InputGroupText>{{ getCurrencySymbol() }}</InputGroupText>
            </InputGroupAddon>
            <InputGroupInput
              id="edit_savings_target"
              type="text"
              inputmode="numeric"
              placeholder="0,00"
              v-model="displayTarget"
              @keydown="filterNumericInput"
            />
          </InputGroup>
          <InputError :message="form.errors.target_amount" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="grid gap-3">
            <Label for="edit_savings_color">{{
              t('generic.labels.color')
            }}</Label>
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
                          getColorClass(form.color, 'picker'),
                        )
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

          <div class="grid gap-3">
            <Label for="edit_savings_icon">{{
              t('generic.labels.icon')
            }}</Label>
            <Select v-model="form.icon">
              <SelectTrigger :class="cn('w-full', form.icon && 'pl-1')">
                <SelectValue
                  :placeholder="t('generic.placeholders.selectIcon')"
                >
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
              <SelectContent>
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
