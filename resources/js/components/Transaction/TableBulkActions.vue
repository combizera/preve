<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CreditCard, Trash } from 'lucide-vue-next';
import { inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import {
  bulkAssignCreditCard,
  bulkDestroy,
} from '@/actions/App/Http/Controllers/TransactionController';
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
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import type { ICreditCard } from '@/types/models/credit-card';

const props = defineProps<{
  selectedIds: string[];
}>();

const emit = defineEmits<{ cleared: [] }>();

const { t } = useI18n();

const creditCards = inject<ICreditCard[]>('creditCards', []);

const showDeleteDialog = ref(false);
const cardSelectValue = ref<number | null>(null);

const deleteForm = useForm<{ ids: string[] }>({ ids: [] });
const assignForm = useForm<{ ids: string[]; credit_card_id: number | null }>({
  ids: [],
  credit_card_id: null,
});

const confirmDelete = () => {
  deleteForm.ids = [...props.selectedIds];

  deleteForm.delete(bulkDestroy().url, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteDialog.value = false;
      emit('cleared');
    },
  });
};

const assignToCard = (cardId: number) => {
  assignForm.ids = [...props.selectedIds];
  assignForm.credit_card_id = cardId;

  assignForm.patch(bulkAssignCreditCard().url, {
    preserveScroll: true,
    onSuccess: () => {
      cardSelectValue.value = null;
      emit('cleared');
    },
  });
};

const onCardSelect = (value: unknown) => {
  if (typeof value === 'number') assignToCard(value);
};
</script>

<template>
  <div
    class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-md border bg-muted/40 px-3 py-2"
  >
    <span class="text-sm text-muted-foreground">
      {{ t('transactions.bulk.selected', { count: selectedIds.length }) }}
    </span>
    <div class="flex items-center gap-2">
      <Select
        v-if="creditCards.length > 0"
        :model-value="cardSelectValue ?? undefined"
        @update:model-value="onCardSelect"
      >
        <SelectTrigger size="sm" class="h-8 w-48">
          <CreditCard :size="14" class="text-muted-foreground" />
          <SelectValue :placeholder="t('transactions.bulk.assignCard')" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectItem
              v-for="card in creditCards"
              :key="card.id"
              :value="card.id"
            >
              {{ card.name }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
      <Button variant="destructive" size="sm" @click="showDeleteDialog = true">
        <Trash :size="14" />
        {{ t('transactions.bulk.delete') }}
      </Button>
    </div>
  </div>

  <AlertDialog v-model:open="showDeleteDialog">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ t('transactions.bulk.confirm', { count: selectedIds.length }) }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>
          {{ t('generic.actions.cancel') }}
        </AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          :disabled="deleteForm.processing"
          @click="confirmDelete"
        >
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
