<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import {
  type DateValue,
  DateFormatter,
  getLocalTimeZone,
  today,
  parseDate,
} from '@internationalized/date';
import { useVModel } from '@vueuse/core';
import { CalendarIcon } from 'lucide-vue-next';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { useLocale } from '@/composables/useLocale';

const props = defineProps<{
  defaultValue?: string;
  modelValue?: string;
  placeholder?: string;
  id?: string;
  class?: HTMLAttributes['class'];
}>();

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string): void;
}>();

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
});

const defaultPlaceholder = today(getLocalTimeZone());

const { locale } = useLocale();

const df = new DateFormatter(locale.value, {
  dateStyle: 'long',
});

const dateValue = computed<DateValue | undefined>({
  get: () => {
    if (!modelValue.value) return undefined;
    try {
      return parseDate(modelValue.value);
    } catch {
      return undefined;
    }
  },
  set: (value: DateValue | undefined) => {
    modelValue.value = value?.toString() ?? '';
  },
});
</script>

<template>
  <Popover v-slot="{ close }">
    <PopoverTrigger as-child>
      <Button
        :id="props.id"
        variant="outline"
        :class="cn('w-full justify-start text-left font-normal', !dateValue && 'text-muted-foreground', props.class)"
      >
        <CalendarIcon />
        {{ dateValue ? df.format(dateValue.toDate(getLocalTimeZone())) : placeholder ?? 'Pick a date' }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" align="start">
      <Calendar
        v-model="dateValue"
        :default-placeholder="defaultPlaceholder"
        layout="month-and-year"
        initial-focus
        @update:model-value="close"
      />
    </PopoverContent>
  </Popover>
</template>
