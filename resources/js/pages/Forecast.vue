<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Plus, Wallet } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import EmptyState from '@/components/EmptyState.vue';
import CardForecast from '@/components/Forecast/CardForecast.vue';
import CreateForecastDialog from '@/components/Forecast/CreateForecastDialog.vue';
import ForecastSummaryCard from '@/components/Forecast/ForecastSummaryCard.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import forecastRoutes from '@/routes/forecasts';
import { useForecastStore } from '@/stores/forecast.store';
import { type BreadcrumbItem } from '@/types';
import type { ICategory } from '@/types/models/category';
import type { IForecast } from '@/types/models/forecast';

interface Props {
  forecasts: IForecast[];
  categories: ICategory[];
}

const props = defineProps<Props>();

const { t } = useI18n();
const forecastStore = useForecastStore();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
  {
    title: t('forecasts.title'),
    href: forecastRoutes.index().url,
  },
]);

const canCreate = computed(() => props.categories.length > 0);

const openCreate = () => forecastStore.openCreateDialog();

onMounted(() => {
  const url = new URL(window.location.href);
  const manage = url.searchParams.get('manage');

  if (!manage) return;

  forecastStore.requestEdit(manage);

  url.searchParams.delete('manage');
  window.history.replaceState({}, '', url.toString());
});
</script>

<template>
  <Head :title="t('forecasts.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex items-center justify-between">
      <Heading
        :title="t('forecasts.title')"
        :description="t('forecasts.description')"
      />
      <Button
        v-if="forecasts.length > 0 && canCreate"
        size="sm"
        @click="openCreate"
      >
        <Plus :size="16" />
        {{ t('forecasts.newForecast') }}
      </Button>
    </div>

    <div class="mt-4">
      <EmptyState
        v-if="forecasts.length === 0"
        :title="t('forecasts.empty.title')"
        :description="t('forecasts.empty.description')"
        :button-text="t('forecasts.newForecast')"
        :show-button="canCreate"
        @action="openCreate"
      >
        <template #icon>
          <Wallet />
        </template>
      </EmptyState>

      <template v-else>
        <ForecastSummaryCard :forecasts="forecasts" class="mb-3" />

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <CardForecast
            v-for="forecast in forecasts"
            :key="forecast.id"
            :forecast="forecast"
            :categories="categories"
          />
        </div>
      </template>
    </div>

    <CreateForecastDialog :categories="categories" />
  </AppLayout>
</template>
