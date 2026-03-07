<script setup lang="ts">
import ToastProvider from '@/components/ToastProvider.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="mx-auto h-full w-full max-w-375 overflow-x-auto p-4">
      <slot />
    </main>
  </AppLayout>

  <ToastProvider />
</template>
