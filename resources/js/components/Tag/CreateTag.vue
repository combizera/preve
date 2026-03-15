<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import QuickCreateCard from '@/components/QuickCreateCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/tags';

const { t } = useI18n();

const form = useForm({
  name: '',
  description: '',
});

const createTag = () => {
  form.submit(store(), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <QuickCreateCard :title="t('tags.newTag')">
    <form class="w-full flex flex-col md:flex-row flex-wrap items-start gap-3" @submit.prevent="createTag">
      <div class="flex flex-col gap-2 justify-start max-md:w-full md:min-w-1/4">
        <Label for="name">{{ t('generic.labels.name') }}</Label>
        <Input id="name" name="name" :placeholder="t('generic.labels.name')" v-model="form.name" />
        <InputError :message="form.errors.name" />
      </div>

      <div class="flex flex-col gap-2 flex-1 justify-start max-md:w-full md:min-w-1/4">
        <Label for="description">{{ t('models.transaction.description') }}</Label>
        <Input id="description" name="description" :placeholder="t('generic.placeholders.describeTag')" v-model="form.description" />
        <InputError :message="form.errors.description" />
      </div>

      <div class="max-lg:w-full h-full flex items-start pt-2 lg:pt-5.5">
        <Button type="submit" :disabled="form.processing" class="h-9 w-full lg:w-auto">
          {{ t('generic.actions.create') }}
        </Button>
      </div>
    </form>
  </QuickCreateCard>
</template>
