<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { type BreadcrumbItem } from '@/types';

const { t } = useI18n();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('settings.preferences.title'),
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('settings.preferences.title')" />

        <h1 class="sr-only">{{ t('settings.preferences.title') }}</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    :title="t('settings.preferences.heading')"
                    :description="t('settings.preferences.description')"
                />
              <h1>
                hello
              </h1>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>
