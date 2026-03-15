<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Banknote, Globe } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLocale } from '@/composables/useLocale';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { preferences } from '@/routes/profile';
import { type BreadcrumbItem } from '@/types';

const { t } = useI18n();
const { locale, supportedLocales, updateLocale } = useLocale();

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
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Globe class="size-4" />
                            {{ t('settings.preferences.language.title') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.preferences.language.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <Label for="locale">{{ t('settings.preferences.language.label') }}</Label>
                            <Select
                                :model-value="locale"
                                @update:model-value="(value) => updateLocale(value as any)"
                            >
                                <SelectTrigger id="locale" class="w-full sm:w-64">
                                    <SelectValue :placeholder="t('settings.preferences.language.placeholder')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="loc in supportedLocales"
                                        :key="loc.value"
                                        :value="loc.value"
                                    >
                                        {{ loc.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <Card class="relative opacity-60">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <CardTitle class="flex items-center gap-2">
                                <Banknote class="size-4" />
                                {{ t('settings.preferences.currency.title') }}
                            </CardTitle>
                            <Badge variant="secondary">
                                {{ t('settings.preferences.currency.comingSoon') }}
                            </Badge>
                        </div>
                        <CardDescription>
                            {{ t('settings.preferences.currency.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <Label class="text-muted-foreground">{{ t('settings.preferences.currency.title') }}</Label>
                            <Select disabled>
                                <SelectTrigger class="w-full sm:w-64">
                                    <SelectValue placeholder="BRL - Real (R$)" />
                                </SelectTrigger>
                                <SelectContent />
                            </Select>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
