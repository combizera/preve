<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
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
import { MONTHS } from '@/lib/calendar';

const emit = defineEmits<{
  (e: 'update:month', payload: { month: number; year: number }): void;
}>();

const MIN_YEAR = 2026;
const MAX_YEAR = 2027;

const now = new Date();
const currentYear = now.getFullYear();
const currentMonth = now.getMonth();

const selectedYear = ref<string>(String(currentYear));
const selectedMonth = ref<number>(currentMonth);
const stripRef = ref<HTMLUListElement | null>(null);

const scrollToSelected = () => {
  nextTick(() => {
    if (!stripRef.value) return;
    const el = stripRef.value as HTMLElement;
    const card = el.children[selectedMonth.value] as HTMLElement;
    if (!card) return;
    card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
  });
};

const navigate = (direction: 'prev' | 'next') => {
  const year = Number(selectedYear.value);

  if (direction === 'prev') {
    if (selectedMonth.value === 0) {
      if (year - 1 < MIN_YEAR) {
        toast.error('You have reached the earliest available year.');
        return;
      }
      selectedMonth.value = 11;
      selectedYear.value = String(year - 1);
    } else {
      selectedMonth.value--;
    }
  } else {
    if (selectedMonth.value === 11) {
      if (year + 1 > MAX_YEAR) {
        toast.error('You have reached the latest available year.');
        return;
      }
      selectedMonth.value = 0;
      selectedYear.value = String(year + 1);
    } else {
      selectedMonth.value++;
    }
  }

  scrollToSelected();
  emit('update:month', { month: selectedMonth.value + 1, year: Number(selectedYear.value) });
};

const emitMonth = (month: number, year: number) => {
  emit('update:month', { month: month + 1, year });
};

const handleYearChange = (newYear: string) => {
  selectedYear.value = String(newYear);
  emitMonth(selectedMonth.value, Number(newYear));
};

const handleToCurrentMonth = () => {
  selectedYear.value = String(currentYear);
  selectedMonth.value = currentMonth;
  scrollToSelected();
  emitMonth(currentMonth, currentYear);
};
</script>

<template>
  <section class="mb-4 p-1 border rounded-lg-outer">
    <div class="flex justify-between items-center gap-2 relative w-full overflow-auto border rounded-lg p-2 px-4">

      <!-- YEAR -->
      <Select :model-value="selectedYear" @update:model-value="handleYearChange">
        <SelectTrigger class="w-[100px]">
          <SelectValue :placeholder="String(currentYear)" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>Year</SelectLabel>
            <SelectItem value="2026">
              2026
            </SelectItem>
            <SelectItem value="2027">
              2027
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>

      <!-- STRIP -->
      <div class="dark:border-sidebar-border rounded-lg p-2 flex items-center gap-0 overflow-x-auto w-full h-20">
        <Button variant="ghost" type="button" class="hover:bg-muted" @click="navigate('prev')">
          <ChevronLeft />
        </Button>

        <ul ref="stripRef" class="w-full h-full py-1 flex items-center gap-0 overflow-hidden scroll-smooth">
          <CardCalendar
            v-for="(month, index) in MONTHS"
            :key="index"
            :month="month"
            :year="Number(selectedYear)"
            :isSelected="index === selectedMonth"
            :isCurrent="index === currentMonth && Number(selectedYear) === currentYear"
            @select="() => { selectedMonth = index; emitMonth(index, Number(selectedYear)); }"
          />
        </ul>

        <Button variant="ghost" type="button" class="hover:bg-muted" @click="navigate('next')">
          <ChevronRight />
        </Button>
      </div>

      <!-- BUTTON -->
      <Button variant="outline" type="button" @click="handleToCurrentMonth"> Today </Button>
    </div>
  </section>
</template>
