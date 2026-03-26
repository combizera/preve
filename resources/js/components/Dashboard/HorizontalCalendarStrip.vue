<script setup lang="ts">
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

import CardCalendar from '@/components/Dashboard/CardCalendar.vue';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { MONTH_KEYS } from '@/lib/calendar';

const { t } = useI18n();

const emit = defineEmits<{
  (e: 'update:month', payload: { month: number; year: number }): void;
}>();

const MIN_YEAR = 2026;
const MAX_YEAR = 2030;

const now = new Date();
const currentYear = now.getFullYear();
const currentMonth = now.getMonth();

const selectedYear = ref<string>(String(currentYear));
const selectedMonth = ref<number>(currentMonth);
const monthCards = ref([]);

const emitMonth = (month: number, year: number) => {
  emit('update:month', { month: month + 1, year });
};

const changeYear = (newYear: number, month: number): boolean => {
  if (newYear < MIN_YEAR || newYear > MAX_YEAR) {
    toast.error(newYear < MIN_YEAR
      ? t('dashboard.calendar.earliestYear')
      : t('dashboard.calendar.latestYear'),
    );
    return false;
  }
  selectedYear.value = String(newYear);
  selectedMonth.value = month;
  return true;
};

const navigate = (direction: 'prev' | 'next') => {
  const year = Number(selectedYear.value);

  if (direction === 'prev') {
    if (selectedMonth.value === 0) {
      if (!changeYear(year - 1, 11)) return;
    } else {
      selectedMonth.value--;
    }
  } else {
    if (selectedMonth.value === 11) {
      if (!changeYear(year + 1, 0)) return;
    } else {
      selectedMonth.value++;
    }
  }

  emitMonth(selectedMonth.value, Number(selectedYear.value));
};

const handleYearChange = (newYear: string) => {
  selectedYear.value = String(newYear);
  emitMonth(selectedMonth.value, Number(newYear));
};

const handleToCurrentMonth = () => {
  if (
    selectedMonth.value === currentMonth &&
    Number(selectedYear.value) === currentYear
  ) {
    return;
  }

  selectedYear.value = String(currentYear);
  selectedMonth.value = currentMonth;

  emitMonth(currentMonth, currentYear);
};

const years = Array.from({ length: MAX_YEAR - MIN_YEAR + 1 }, (_, i) =>
  String(MIN_YEAR + i),
);

watch(
  () => selectedMonth.value,
  async (newIndex) => {
    await nextTick();

    const targetCard = monthCards.value[newIndex];

    // @ts-expect-error no type for $el
    const el = targetCard?.$el || targetCard;

    if (el) {
      el.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'center',
      });
    }
  },
);
</script>

<template>
  <section class="double-border flex flex-col bg-sidebar">
    <!-- HEADER -->
    <div class="p-2 px-4 pb-3">
      <div class="flex items-center gap-2 text-muted-foreground">
        <CalendarDays class="size-4" />
        <p class="text-sm">{{ t('dashboard.calendar.title') }}</p>
      </div>
    </div>

    <!-- CONTENT -->
    <div
      class="flex w-full items-center justify-between gap-2 overflow-auto rounded-lg border bg-background p-2 px-4"
    >
      <!-- YEAR -->
      <Select
        :model-value="selectedYear"
        @update:model-value="handleYearChange"
      >
        <SelectTrigger class="w-25">
          <SelectValue :placeholder="String(currentYear)" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>{{ t('dashboard.calendar.year') }}</SelectLabel>
            <SelectItem v-for="year in years" :value="year" :key="year">
              {{ year }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>

      <!-- STRIP -->
      <div
        class="flex h-20 w-full items-center gap-0 overflow-x-auto rounded-lg p-2"
      >
        <Button
          variant="ghost"
          type="button"
          class="hover:bg-muted"
          @click="navigate('prev')"
        >
          <ChevronLeft />
        </Button>

        <div
          class="h-full w-full overflow-x-auto scroll-smooth py-1"
        >
          <div
            class="mx-auto flex h-full w-max items-center justify-start gap-0"
          >
            <CardCalendar
              v-for="(key, index) in MONTH_KEYS"
              :key="index"
              ref="monthCards"
              :month="t(`dashboard.calendar.months.${key}`)"
              :year="Number(selectedYear)"
              :isSelected="index === selectedMonth"
              :isCurrent="
                index === currentMonth && Number(selectedYear) === currentYear
              "
              @select="
                () => {
                  if (index === selectedMonth) return;
                  selectedMonth = index;
                  emitMonth(index, Number(selectedYear));
                }
              "
            />
          </div>
        </div>

        <Button
          variant="ghost"
          type="button"
          class="hover:bg-muted"
          @click="navigate('next')"
        >
          <ChevronRight />
        </Button>
      </div>

      <!-- BUTTON -->
      <Button variant="outline" type="button" @click="handleToCurrentMonth">
        {{ t('generic.actions.today') }}
      </Button>
    </div>
  </section>
</template>
