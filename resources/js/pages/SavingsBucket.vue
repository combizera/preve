<script setup lang="ts">
import { Deferred, Head } from '@inertiajs/vue3';
import { ArrowLeftRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Heading from '@/components/Heading.vue';
import ContainerSavingsBucket from '@/components/SavingsBucket/ContainerSavingsBucket.vue';
import CreateSavingsBucket from '@/components/SavingsBucket/CreateSavingsBucket.vue';
import SaveOrWithdrawDialog from '@/components/SavingsBucket/SaveOrWithdrawDialog.vue';
import SavingsYearChart from '@/components/SavingsBucket/SavingsYearChart.vue';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import savings from '@/routes/savings';
import type { BreadcrumbItem } from '@/types';
import type { ICategory } from '@/types/models/category';
import type { ISavingsBucket } from '@/types/models/savings-bucket';
import type { ISavingsMonth } from '@/types/models/savings-history';

interface Props {
  savingsBuckets: ISavingsBucket[];
  availableYears: number[];
  selectedYear: number;
  chartData?: ISavingsMonth[];
  categories?: ICategory[];
}

const props = defineProps<Props>();

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

    <Deferred v-if="props.savingsBuckets.length > 0" data="chartData">
      <template #fallback>
        <Skeleton class="h-[320px] w-full" />
      </template>
      <SavingsYearChart
        :chart-data="chartData ?? []"
        :available-years="availableYears"
        :selected-year="selectedYear"
      />
    </Deferred>

    <Deferred data="categories">
      <template #fallback><span /></template>
      <SaveOrWithdrawDialog
        v-model:open="showSaveOrWithdrawDialog"
        :savings-buckets="savingsBuckets"
        :categories="categories ?? []"
      />
    </Deferred>
  </AppLayout>
</template>
