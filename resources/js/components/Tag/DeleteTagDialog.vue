<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

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
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete
          the tag "{{ tagStore.tag?.name }}".
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>
          Cancel
        </AlertDialogCancel>
        <AlertDialogAction variant="destructive" @click="handleDeleteTag" :disabled="form.processing">
          Confirm
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
