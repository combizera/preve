<script setup lang="ts">
import { Banknote } from 'lucide-vue-next';
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
import { useCurrency } from '@/composables/useCurrency';
import type { SupportedCurrency } from '@/config/currency';

const { t } = useI18n();
const { currency, supportedCurrencies, updateCurrency } = useCurrency();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Banknote class="size-4" />
                {{ t('settings.preferences.currency.title') }}
            </CardTitle>
            <CardDescription>
                {{ t('settings.preferences.currency.description') }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div class="grid gap-2">
                <Label for="currency">{{ t('settings.preferences.currency.title') }}</Label>
                <Select
                    :model-value="currency"
                    @update:model-value="(value: string) => updateCurrency(value as SupportedCurrency)"
                >
                    <SelectTrigger id="currency" class="w-full sm:w-64">
                        <SelectValue :placeholder="t('settings.preferences.currency.placeholder')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="curr in supportedCurrencies"
                            :key="curr.value"
                            :value="curr.value"
                        >
                            {{ curr.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </CardContent>
    </Card>
</template>
