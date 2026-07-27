<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { complete } from '@/routes/reconciliation';

const { t } = useI18n();

const form = useForm({});

const submit = () => {
  form.submit(complete());
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <CheckCircle2 :size="20" class="text-emerald-600" />
        {{ t('reconciliation.summaryStep.title') }}
      </CardTitle>
      <CardDescription>
        {{ t('reconciliation.summaryStep.description') }}
      </CardDescription>
    </CardHeader>
    <CardContent>
      <ul class="grid gap-1.5 text-sm text-muted-foreground">
        <li>· {{ t('reconciliation.summaryStep.itemRecurring') }}</li>
        <li>· {{ t('reconciliation.summaryStep.itemBuckets') }}</li>
        <li>· {{ t('reconciliation.summaryStep.itemBalance') }}</li>
      </ul>
    </CardContent>
    <CardFooter class="justify-end">
      <Button type="button" @click="submit" :disabled="form.processing">
        {{ t('reconciliation.summaryStep.finish') }}
      </Button>
    </CardFooter>
  </Card>
</template>
