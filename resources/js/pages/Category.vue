<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import TableCategory from '@/components/Category/TableCategory.vue';
import Heading from '@/components/Heading.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import categoryRoutes from '@/routes/categories';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    categories: Category[];
}>();

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
        <div
            class="flex h-full flex-1 flex-col gap-2 overflow-x-auto rounded-xl p-4"
        >
            <!-- HEADING -->
            <Heading
                title="Category"
                description="Manage your categories here."
            />

            <!-- TABLE - CRUD -->
            <TableCategory :categories />

            <!-- PLACEHOLDER -->
            <div
                class="relative min-h-full flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
