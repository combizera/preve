<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Preferences settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile Settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Choose your language"
                    description="Select the language you want to use in the application"
                />
              <h1>
                hello
              </h1>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>
