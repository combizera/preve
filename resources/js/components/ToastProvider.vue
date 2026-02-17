<script lang="ts" setup>
import { router } from '@inertiajs/vue3';
import { onUnmounted } from 'vue';
import { toast } from 'vue-sonner';

import { Toaster } from '@/components/ui/sonner';

type MessageToast = {
  type: 'message';
  message: string;
  description: string;
};

type StatusToast = {
  type: 'default' | 'success' | 'error' | 'warning' | 'info';
  message: string;
};

type Toast = MessageToast | StatusToast;

onUnmounted(
  router.on('flash', (event) => {
    const flash = event.detail.flash.toast as Toast | undefined;

    if (!flash) return;

    switch (flash.type) {
      case 'message':
        toast.message(flash.message, {
          description: flash.description,
        });
        break;
      case 'default':
        toast(flash.message);
        break;
      default:
        toast[flash.type](flash.message);
    }
  })
);
</script>

<template>
  <Toaster position="top-right" />
</template>
