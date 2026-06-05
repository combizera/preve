<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { Button } from '@/components/ui/button';
import {
  formatMonthLabel,
  getAdjacentMonth,
  getCurrentMonthString,
} from '@/lib/forecast';
import forecastRoutes from '@/routes/forecasts';

const props = defineProps<{
  month: string;
}>();

const { t, locale } = useI18n();

const monthLabel = computed(() => formatMonthLabel(props.month, locale.value));
const isCurrentMonth = computed(() => props.month === getCurrentMonthString());

const goToMonth = (month: string) => {
  router.get(
    forecastRoutes.index().url,
    { month },
    { preserveScroll: true, preserveState: true, replace: true },
  );
};
</script>

<template>
  <div class="flex items-center gap-1">
    <Button
      variant="outline"
      size="icon"
      :aria-label="t('forecasts.navigator.previous')"
      @click="goToMonth(getAdjacentMonth(month, -1))"
    >
      <ChevronLeft :size="16" />
    </Button>

    <span
      class="min-w-36 text-center text-sm font-medium text-foreground capitalize"
    >
      {{ monthLabel }}
    </span>

    <Button
      variant="outline"
      size="icon"
      :aria-label="t('forecasts.navigator.next')"
      @click="goToMonth(getAdjacentMonth(month, 1))"
    >
      <ChevronRight :size="16" />
    </Button>

    <Button
      v-if="!isCurrentMonth"
      variant="outline"
      type="button"
      size="sm"
      class="ml-1"
      @click="goToMonth(getCurrentMonthString())"
    >
      {{ t('generic.actions.today') }}
    </Button>
  </div>
</template>
