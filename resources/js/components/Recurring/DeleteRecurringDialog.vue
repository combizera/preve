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
import { destroy } from '@/routes/recurring';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  recurringTransaction: IRecurringTransaction | null;
}>();

const { t } = useI18n();
const form = useForm({});

const deleteRecurring = () => {
  const recurringTransaction = props.recurringTransaction;
  if (!recurringTransaction?.id) return;

  form.submit(destroy(recurringTransaction.id), {
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
          {{ t('generic.confirm.deleteRecurring', { description: recurringTransaction?.description }) }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel> {{ t('generic.actions.cancel') }} </AlertDialogCancel>
        <AlertDialogAction variant="destructive" @click="deleteRecurring" :disabled="form.processing">
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
