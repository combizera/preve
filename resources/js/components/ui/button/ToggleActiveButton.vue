<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pause, Play } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { toggle } from '@/routes/recurring';

const { t } = useI18n();

interface Props {
  id: string | number;
  isActive: boolean;
}

const props = defineProps<Props>();

const toggleActive = () => {
  router.patch(toggle(props.id).url);
};
</script>

<template>
  <DropdownMenuItem @click="toggleActive">
    <Pause v-if="isActive" :size="14" class="fill-foreground stroke-1" />
    <Play v-else :size="14" class="fill-foreground" />
    {{ isActive ? t('generic.actions.pause') : t('generic.actions.resume') }}
  </DropdownMenuItem>
</template>
