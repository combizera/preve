<script setup lang="ts">
import { Globe } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

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
import type { SupportedLocale } from '@/plugins/i18n';

const { t } = useI18n();
const { locale, supportedLocales, updateLocale } = useLocale();
</script>

<template>
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
                    :model-value="(locale as string)"
                    @update:model-value="(value: string) => updateLocale(value as SupportedLocale)"
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
</template>
