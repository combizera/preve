<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <AuthBase
    :title="t('register.title')"
    :description="t('register.description')"
  >
    <Head title="Register" />

    <Form
      v-bind="store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-6">
        <div class="grid gap-2">
          <Label for="name">
            {{ t('generic.name') }}
          </Label>
          <Input
            id="name"
            type="text"
            required
            autofocus
            :tabindex="1"
            autocomplete="name"
            name="name"
            :placeholder="t('generic.fullName')"
          />
          <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
          <Label for="email">{{ t('generic.email') }}</Label>
          <Input
            id="email"
            type="email"
            required
            :tabindex="2"
            autocomplete="email"
            name="email"
            :placeholder="t('messages.emailPlaceholder')"
          />
          <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
          <Label for="password">{{ t('generic.password') }}</Label>
          <Input
            id="password"
            type="password"
            required
            :tabindex="3"
            autocomplete="new-password"
            name="password"
            placeholder="*********"
          />
          <InputError :message="errors.password" />
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation">{{ t('generic.confirmPassword') }}</Label>
          <Input
            id="password_confirmation"
            type="password"
            required
            :tabindex="4"
            autocomplete="new-password"
            name="password_confirmation"
            placeholder="*********"
          />
          <InputError :message="errors.password_confirmation" />
        </div>

        <Button
          type="submit"
          class="mt-2 w-full"
          tabindex="5"
          :disabled="processing"
          data-test="register-user-button"
        >
          <Spinner v-if="processing" />
          {{ t('register.buttonText') }}
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        {{ t('register.haveAccount') }}
        <TextLink
          :href="login()"
          class="underline underline-offset-4"
          :tabindex="6"
        >{{ t('generic.login') }}</TextLink
        >
      </div>
    </Form>
  </AuthBase>
</template>
