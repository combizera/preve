<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { CalendarOff, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import RegisterHiatusDialog from '@/components/Hiatus/RegisterHiatusDialog.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { show as welcomeBack } from '@/routes/reconciliation';
import { dismiss } from '@/routes/reengagement';
import type { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();
const reengagement = computed(() => page.props.reengagement);

const { t } = useI18n();

const hiatusDialogOpen = ref(false);

const dismissForm = useForm({});

const dismissBanner = () => {
  dismissForm.submit(dismiss(), { preserveScroll: true });
};
</script>

<template>
  <div v-if="reengagement" class="mb-4">
    <Alert
      class="grid-cols-[auto_1fr] gap-x-3 border-amber-500/40 bg-amber-500/10 pr-10 dark:border-amber-400/30"
    >
      <div
        class="row-span-2 flex size-9 items-center justify-center self-center rounded-lg bg-amber-500/15 dark:bg-amber-400/10"
      >
        <CalendarOff :size="18" class="text-amber-600 dark:text-amber-300" />
      </div>
      <AlertTitle class="text-amber-900 dark:text-amber-200">
        {{
          t('reengagement.banner.title', { days: reengagement.inactive_days })
        }}
      </AlertTitle>
      <AlertDescription>
        <p>{{ t('reengagement.banner.description') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
          <Button
            size="sm"
            class="from-amber-600 to-amber-500 text-white dark:from-amber-500 dark:to-amber-400 dark:text-amber-950"
            as-child
          >
            <Link :href="welcomeBack().url">
              {{ t('reengagement.banner.cta') }}
            </Link>
          </Button>
          <Button size="sm" variant="outline" @click="hiatusDialogOpen = true">
            {{ t('reengagement.banner.registerHiatus') }}
          </Button>
        </div>
      </AlertDescription>
      <Button
        variant="ghost"
        size="icon"
        class="absolute top-2 right-2 size-7 hover:bg-amber-500/15"
        :disabled="dismissForm.processing"
        :aria-label="t('reengagement.banner.dismiss')"
        @click="dismissBanner"
      >
        <X :size="16" />
      </Button>
    </Alert>

    <RegisterHiatusDialog
      v-model:open="hiatusDialogOpen"
      :suggested-start="reengagement.last_manual_activity"
    />
  </div>
</template>
