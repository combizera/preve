<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowLeftRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Heading from '@/components/Heading.vue';
import ContainerSavingsBucket from '@/components/SavingsBucket/ContainerSavingsBucket.vue';
import CreateSavingsBucket from '@/components/SavingsBucket/CreateSavingsBucket.vue';
import SaveOrWithdrawDialog from '@/components/SavingsBucket/SaveOrWithdrawDialog.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import savings from '@/routes/savings';
import type { BreadcrumbItem } from '@/types';
import type { ICategory } from '@/types/models/category';
import type { ISavingsBucket } from '@/types/models/savings-bucket';

interface Props {
  savingsBuckets: ISavingsBucket[];
  categories: ICategory[];
}

defineProps<Props>();

const { t } = useI18n();

const showSaveOrWithdrawDialog = ref(false);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: t('dashboard.title'),
    href: dashboard().url,
  },
  {
    title: t('savings.title'),
    href: savings.index().url,
  },
]);
</script>

<template>
  <Head :title="t('savings.title')" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <Heading
      :title="t('savings.title')"
      :description="t('savings.description')"
      :hasActions="savingsBuckets.length > 0"
    >
      <Button @click="showSaveOrWithdrawDialog = true">
        <ArrowLeftRight :size="16" />
        {{ t('savings.saveOrWithdraw.trigger') }}
      </Button>
    </Heading>

    <CreateSavingsBucket />

    <ContainerSavingsBucket :savings-buckets="savingsBuckets" />

    <SaveOrWithdrawDialog
      v-model:open="showSaveOrWithdrawDialog"
      :savings-buckets="savingsBuckets"
      :categories="categories"
    />
  </AppLayout>
</template>
