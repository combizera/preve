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
import { destroy } from '@/routes/savings';
import { useSavingsBucketStore } from '@/stores/savings-bucket.store';

const { t } = useI18n();
const store = useSavingsBucketStore();

const form = useForm({});
const typedName = ref('');
const justCopied = ref(false);
let copyResetTimer: ReturnType<typeof setTimeout> | null = null;

const canDelete = computed(
  () => !!store.bucket && typedName.value.trim() === store.bucket.name,
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

async function copyBucketName() {
  if (!store.bucket) return;
  await navigator.clipboard.writeText(store.bucket.name);
  justCopied.value = true;
  if (copyResetTimer) clearTimeout(copyResetTimer);
  copyResetTimer = setTimeout(() => (justCopied.value = false), 1500);
}

function handleDelete() {
  if (!store.bucket || !canDelete.value) return;

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

      <div class="grid gap-2">
        <Label for="delete-bucket-confirm">
          {{
            t('savings.deleteConfirm.label', { name: store.bucket?.name ?? '' })
          }}
        </Label>
        <div class="flex gap-2">
          <Input
            id="delete-bucket-confirm"
            v-model="typedName"
            autocomplete="off"
            :placeholder="store.bucket?.name"
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
            @click="copyBucketName"
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
