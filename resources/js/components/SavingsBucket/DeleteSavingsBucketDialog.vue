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
import { destroy } from '@/routes/savings';
import { useSavingsBucketStore } from '@/stores/savings-bucket.store';

const { t } = useI18n();
const store = useSavingsBucketStore();

const form = useForm({});

function handleDelete() {
  if (!store.bucket) return;

  form.submit(destroy(store.bucket.id), {
    preserveScroll: true,
    onSuccess: () => store.closeDeleteModal(),
    onError: () => store.closeDeleteModal(),
  });
}
</script>

<template>
  <AlertDialog v-model:open="store.showDeleteDialog">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{
            t('generic.confirm.deleteSavingsBucket', {
              name: store.bucket?.name,
            })
          }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>
          {{ t('generic.actions.cancel') }}
        </AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          :disabled="form.processing"
          @click="handleDelete"
        >
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
