<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { getLocalTimeZone, today } from '@internationalized/date';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
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
import { store } from '@/routes/hiatuses';
import type { IHiatusInput } from '@/types/models/hiatus';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  suggestedStart?: string | null;
}>();

const { t } = useI18n();

const form = useForm<IHiatusInput>({
  started_at: props.suggestedStart ?? today(getLocalTimeZone()).toString(),
  ended_at: today(getLocalTimeZone()).toString(),
  note: null,
});

const submit = () => {
  form.submit(store(), {
    preserveScroll: true,
    onSuccess: () => {
      open.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <Dialog v-model:open="open">
    <DialogContent class="sm:max-w-106.25">
      <DialogHeader>
        <DialogTitle>{{ t('reengagement.hiatusDialog.title') }}</DialogTitle>
        <DialogDescription>
          {{ t('reengagement.hiatusDialog.description') }}
        </DialogDescription>
      </DialogHeader>

      <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-3">
          <Label for="started_at">{{ t('generic.labels.startDate') }}</Label>
          <DatePicker id="started_at" v-model="form.started_at" />
          <InputError :message="form.errors.started_at" />
        </div>

        <div class="grid gap-3">
          <Label for="ended_at">{{ t('generic.labels.endDate') }}</Label>
          <DatePicker id="ended_at" v-model="form.ended_at" />
          <InputError :message="form.errors.ended_at" />
        </div>
      </div>

      <div class="grid gap-3">
        <Label for="note" class="text-muted-foreground">
          {{ t('generic.labels.notesOptional') }}
        </Label>
        <Input
          id="note"
          :placeholder="t('reengagement.hiatusDialog.notePlaceholder')"
          v-model="form.note"
        />
        <InputError :message="form.errors.note" />
      </div>

      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">{{ t('generic.actions.cancel') }}</Button>
        </DialogClose>
        <Button type="button" @click="submit" :disabled="form.processing">
          {{ t('reengagement.hiatusDialog.submit') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
