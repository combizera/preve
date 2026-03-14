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
import { destroy } from '@/routes/transactions';
import { ITransaction } from '@/types/models/transaction';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  transaction: ITransaction | null;
}>();

const { t } = useI18n();
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
        <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ t('generic.confirm.deleteTransaction', { description: transaction?.description }) }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel> {{ t('generic.actions.cancel') }} </AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          @click="deleteTransaction"
          :disabled="form.processing"
        >
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
