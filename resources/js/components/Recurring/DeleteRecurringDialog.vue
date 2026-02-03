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
import { destroy } from '@/routes/recurring';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  recurringTransaction: IRecurringTransaction | null;
}>();

const form = useForm({});

const deleteRecurring = () => {
  const recurringTransaction = props.recurringTransaction;
  if (!recurringTransaction?.id) return;

  const id = Number(recurringTransaction.id);

  form.submit(destroy(id), {
    onSuccess: () => {
      open.value = false;
    },
  });
};
</script>

<template>
  <AlertDialog v-model:open="open">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
        <AlertDialogDescription>
          This action cannot be undone. This will permanently delete the
          transaction "{{ recurringTransaction?.description }}".
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel> Cancel </AlertDialogCancel>
        <AlertDialogAction variant="destructive" @click="deleteRecurring" :disabled="form.processing">
          Confirm
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
