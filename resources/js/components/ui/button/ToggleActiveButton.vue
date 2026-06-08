<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pause, Play } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';

const { t } = useI18n();

interface Props {
  toggleUrl: string;
  isActive: boolean;
}

const props = defineProps<Props>();

const toggleActive = () => {
  router.patch(props.toggleUrl);
};
</script>

<template>
  <DropdownMenuItem @click="toggleActive">
    <Pause v-if="isActive" :size="14" class="fill-foreground stroke-1" />
    <Play v-else :size="14" class="fill-foreground" />
    {{ isActive ? t('generic.actions.pause') : t('generic.actions.resume') }}
  </DropdownMenuItem>
</template>
