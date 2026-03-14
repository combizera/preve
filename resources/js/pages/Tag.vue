<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import Heading from '@/components/Heading.vue';
import ContainerTag from '@/components/Tag/ContainerTag.vue';
import CreateTag from '@/components/Tag/CreateTag.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import tagRoutes from '@/routes/tags';
import { type BreadcrumbItem } from '@/types';
import type { IPaginate } from '@/types/models/paginated';
import type { ITag } from '@/types/models/tag';

interface Props {
  tags: IPaginate<ITag>;
}

defineProps<Props>()

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
  {
    title: t('tags.title'),
    href: tagRoutes.index().url,
  },
]);
</script>

<template>
  <Head :title="t('tags.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- HEADING -->
    <Heading :title="t('tags.title')" :description="t('tags.description')" />

    <!-- CREATE -->
    <CreateTag />

    <!-- CONTAINER -->
    <ContainerTag :tags="tags" />
  </AppLayout>
</template>
