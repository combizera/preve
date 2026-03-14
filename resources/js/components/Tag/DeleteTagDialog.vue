<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { destroy } from '@/routes/tags';
import { useTagStore } from '@/stores/tag.store';

const { t } = useI18n();

const tagStore = useTagStore()

const form = useForm({});

function handleDeleteTag() {
  if (!tagStore.tag) return;

  form.submit(destroy(tagStore.tag.id), {
    onSuccess: () => {
      tagStore.closeDeleteModal()
    }
  });
};
</script>

<template>
  <AlertDialog v-model:open="tagStore.showDeleteDialog">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ t('generic.confirm.deleteTag', { name: tagStore.tag?.name }) }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>
          {{ t('generic.actions.cancel') }}
        </AlertDialogCancel>
        <AlertDialogAction variant="destructive" @click="handleDeleteTag" :disabled="form.processing">
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
