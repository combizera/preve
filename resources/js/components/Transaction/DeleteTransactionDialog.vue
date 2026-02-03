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
import { destroy } from '@/routes/transactions';
import { ITransaction } from '@/types/models/transaction';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  transaction: ITransaction | null;
}>();

const form = useForm({});

const deleteTransaction = () => {
  const transaction = props.transaction;
  if (!transaction?.id) return;

  form.submit(destroy(transaction.id), {
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
          transaction "{{ transaction?.description }}".
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel> Cancel </AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          @click="deleteTransaction"
          :disabled="form.processing"
        >
          Confirm
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
