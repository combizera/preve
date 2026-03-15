<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

const { t } = useI18n();

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        :title="t('auth.verifyEmail.title')"
        :description="t('auth.verifyEmail.description')"
    >
        <Head :title="t('auth.verifyEmail.title')" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ t('auth.verifyEmail.verificationSent') }}
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                {{ t('auth.verifyEmail.resendButton') }}
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                {{ t('auth.verifyEmail.logOut') }}
            </TextLink>
        </Form>
    </AuthLayout>
</template>
