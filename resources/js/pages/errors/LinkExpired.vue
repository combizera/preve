<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link2Off } from 'lucide-vue-next';
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

defineProps<{
  status?: number;
}>();

const { t } = useI18n();
</script>

<template>
  <Head :title="t('errors.linkExpired.title')" />

  <div class="flex min-h-screen items-center justify-center bg-background p-4">
    <Card class="w-full max-w-md text-center shadow-lg">
      <CardHeader class="flex flex-col items-center space-y-3 pb-2">
        <div class="rounded-full bg-muted p-3">
          <Link2Off class="size-8 text-muted-foreground" />
        </div>
        <div class="space-y-1">
          <CardTitle class="text-2xl font-bold tracking-tight">
            {{ t('errors.linkExpired.title') }}
          </CardTitle>
          <CardDescription>
            {{ t('errors.linkExpired.subtitle', { status: status ?? 403 }) }}
          </CardDescription>
        </div>
      </CardHeader>

      <CardContent>
        <p class="text-sm text-muted-foreground">
          {{ t('errors.linkExpired.description') }}
        </p>
      </CardContent>

      <CardFooter class="flex justify-center pb-6">
        <Button as="a" href="/">
          {{ t('errors.linkExpired.backToHome') }}
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>
