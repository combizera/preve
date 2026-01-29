<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import ContainerCategory from '@/components/Category/ContainerCategory.vue';
import CreateCategory from '@/components/Category/CreateCategory.vue';
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

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Category',
    href: categoryRoutes.index().url,
  },
];
</script>

<template>
  <Head title="Category" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="page-container">
      <!-- HEADING -->
      <Heading title="Category" description="Manage your categories here." />

      <!-- CREATE -->
      <CreateCategory />

      <!-- CONTAINER -->
      <ContainerCategory :incomeCategories :expenseCategories />
    </div>
  </AppLayout>
</template>
