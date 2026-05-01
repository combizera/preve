<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import ContainerCategory from '@/components/Category/ContainerCategory.vue';
import CreateCategory from '@/components/Category/CreateCategory.vue';
import CreateForecastDialog from '@/components/Forecast/CreateForecastDialog.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import categoryRoutes from '@/routes/categories';
import { type BreadcrumbItem } from '@/types';
import { type ICategory } from '@/types/models/category';

interface Props {
  incomeCategories: ICategory[];
  expenseCategories: ICategory[];
}

const props = defineProps<Props>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
  {
    title: t('categories.title'),
    href: categoryRoutes.index().url,
  },
]);

const availableForecastCategories = computed<ICategory[]>(() =>
  props.expenseCategories.filter((category) => !category.forecast_series),
);
</script>

<template>
  <Head :title="t('categories.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- HEADING -->
    <Heading :title="t('categories.title')" :description="t('categories.description')" />

    <!-- CREATE -->
    <CreateCategory />

    <!-- CONTAINER -->
    <ContainerCategory :incomeCategories :expenseCategories />

    <CreateForecastDialog :categories="availableForecastCategories" />
  </AppLayout>
</template>
