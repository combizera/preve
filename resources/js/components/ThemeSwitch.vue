<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();
const { t } = useI18n();

const onUpdate = (value: string | number) => {
  updateAppearance(value as 'light' | 'dark' | 'system');
};
</script>

<template>
  <ToggleGroup :model-value="appearance" class="h-7 p-0.5" @update:model-value="onUpdate">
    <ToggleGroupItem value="system" class="h-full px-2" :aria-label="t('generic.sidebar.systemTheme')">
      <Monitor :size="14" :stroke-width="2" />
    </ToggleGroupItem>
    <ToggleGroupItem value="light" class="h-full px-2" :aria-label="t('generic.sidebar.lightTheme')">
      <Sun :size="14" :stroke-width="2" />
    </ToggleGroupItem>
    <ToggleGroupItem value="dark" class="h-full px-2" :aria-label="t('generic.sidebar.darkTheme')">
      <Moon :size="14" :stroke-width="2" />
    </ToggleGroupItem>
  </ToggleGroup>
</template>
