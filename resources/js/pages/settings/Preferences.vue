<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import CurrencyCard from '@/components/Preferences/CurrencyCard.vue';
import LanguageCard from '@/components/Preferences/LanguageCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { preferences } from '@/routes/profile';
import { type BreadcrumbItem } from '@/types';

const { t } = useI18n();

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: t('settings.preferences.title'),
    href: preferences().url,
  },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head :title="t('settings.preferences.title')" />

    <h1 class="sr-only">{{ t('settings.preferences.title') }}</h1>

    <SettingsLayout>
      <div class="flex flex-col space-y-6">
        <!-- LANGUAGE -->
        <LanguageCard />

        <!-- CURRENCY -->
        <CurrencyCard />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
