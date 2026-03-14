<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/tags';
import { useTagStore } from '@/stores/tag.store';

const { t } = useI18n();

const tagStore = useTagStore();

const form = useForm({
  name: '',
  description: '',
});

watch(
  () => tagStore.tag,
  (newTag) => {
    if (newTag) {
      form.name = newTag.name;
      form.description = newTag.description ?? '';
      form.clearErrors();
    } else {
      form.reset();
    }
  },
);

function handleUpdateTag() {
  if (!tagStore.tag) return;

  form.submit(update(tagStore.tag.id), {
    onSuccess: () => {
      tagStore.closeEditModal();
    },
  });
}

function handleOpenUpdate(open: boolean) {
  if (!open) {
    tagStore.closeEditModal();
  }
}
</script>

<template>
  <Dialog
    v-model:open="tagStore.showEditDialog"
    @update:open="handleOpenUpdate"
  >
    <form>
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>{{ t('tags.edit.title') }}</DialogTitle>
          <DialogDescription>
            {{ t('tags.edit.description', { name: tagStore.tag?.name }) }}
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4">
          <div class="grid gap-3">
            <Label for="name">{{ t('generic.labels.name') }}</Label>
            <Input
              id="name"
              name="name"
              :placeholder="t('generic.placeholders.tagName')"
              v-model="form.name"
            />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-3">
            <Label for="description">{{ t('models.transaction.description') }}</Label>
            <Input
              id="description"
              name="description"
              :placeholder="t('generic.placeholders.tagDescription')"
              v-model="form.description"
            />
            <InputError :message="form.errors.description" />
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
          </DialogClose>
          <Button @click="handleUpdateTag" :disabled="form.processing">
            {{ t('generic.actions.saveChanges') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
