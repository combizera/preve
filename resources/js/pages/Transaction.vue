<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
  <Head title="Category" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="mx-auto flex h-full w-full max-w-[1500px] flex-1 flex-col gap-2 overflow-x-auto rounded-xl p-4"
    >
      <!-- HEADING -->
      <Heading title="Transaction" description="Manage your transactions here." />

      <!-- PLACEHOLDER -->
      <div
        class="relative min-h-full flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
      >
        <PlaceholderPattern />
      </div>
    </div>
  </AppLayout>
</template>

<style scoped></style>
