<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Check, Copy } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy } from '@/routes/credit-cards';
import { useCreditCardStore } from '@/stores/credit-card.store';

const { t } = useI18n();
const store = useCreditCardStore();

const form = useForm({});
const typedName = ref('');
const justCopied = ref(false);
let copyResetTimer: ReturnType<typeof setTimeout> | null = null;

const canDelete = computed(
  () => !!store.creditCard && typedName.value.trim() === store.creditCard.name,
);

watch(
  () => store.showDeleteDialog,
  (open) => {
    if (!open) {
      typedName.value = '';
      justCopied.value = false;
      if (copyResetTimer) clearTimeout(copyResetTimer);
    }
  },
);

async function copyCardName() {
  if (!store.creditCard) return;
  await navigator.clipboard.writeText(store.creditCard.name);
  justCopied.value = true;
  if (copyResetTimer) clearTimeout(copyResetTimer);
  copyResetTimer = setTimeout(() => (justCopied.value = false), 1500);
}

function handleDelete() {
  if (!store.creditCard || !canDelete.value) return;

  form.submit(destroy(store.creditCard.id), {
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
            t('generic.confirm.deleteCreditCard', {
              name: store.creditCard?.name,
            })
          }}
        </AlertDialogDescription>
      </AlertDialogHeader>

      <div class="grid gap-2">
        <Label for="delete-card-confirm">
          {{
            t('creditCards.deleteConfirm.label', {
              name: store.creditCard?.name ?? '',
            })
          }}
        </Label>
        <div class="flex gap-2">
          <Input
            id="delete-card-confirm"
            v-model="typedName"
            autocomplete="off"
            :placeholder="store.creditCard?.name"
          />
          <Button
            type="button"
            variant="outline"
            size="icon"
            :aria-label="
              justCopied
                ? t('generic.actions.copied')
                : t('generic.actions.copy')
            "
            :title="
              justCopied
                ? t('generic.actions.copied')
                : t('generic.actions.copy')
            "
            @click="copyCardName"
          >
            <Check v-if="justCopied" class="size-4 text-emerald-500" />
            <Copy v-else class="size-4" />
          </Button>
        </div>
      </div>

      <AlertDialogFooter>
        <AlertDialogCancel>
          {{ t('generic.actions.cancel') }}
        </AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          :disabled="!canDelete || form.processing"
          @click="handleDelete"
        >
          {{ t('generic.actions.confirm') }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
