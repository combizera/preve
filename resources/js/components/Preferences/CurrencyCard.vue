<script setup lang="ts">
import { Banknote } from 'lucide-vue-next';
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
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const { t } = useI18n();
</script>

<template>
    <Card class="opacity-60">
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
</template>
