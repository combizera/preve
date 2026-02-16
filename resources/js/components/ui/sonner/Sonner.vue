<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { CircleCheckIcon, InfoIcon, Loader2Icon, OctagonXIcon, TriangleAlertIcon, XIcon } from "lucide-vue-next"
import { Toaster as Sonner } from "vue-sonner"
import { cn } from "@/lib/utils"
import { toast } from 'vue-sonner'
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();

router.on('flash', (event) => {
  const flash = event.detail.flash as { type?: string; message?: string };

  if (!flash.message) return;

  switch (flash.type) {
    case 'success': toast.success(flash.message); break;
    case 'error':   toast.error(flash.message);   break;
    case 'warning': toast.warning(flash.message); break;
    case 'info':    toast.info(flash.message);    break;
    default:        toast(flash.message);
  }
})

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    :class="cn('toaster group', props.class)"
    :style="{
      '--normal-bg': 'var(--popover)',
      '--normal-text': 'var(--popover-foreground)',
      '--normal-border': 'var(--border)',
      '--border-radius': 'var(--radius)',
    }"
    v-bind="props"
  >
    <template #success-icon>
      <CircleCheckIcon class="size-4" />
    </template>
    <template #info-icon>
      <InfoIcon class="size-4" />
    </template>
    <template #warning-icon>
      <TriangleAlertIcon class="size-4" />
    </template>
    <template #error-icon>
      <OctagonXIcon class="size-4" />
    </template>
    <template #loading-icon>
      <div>
        <Loader2Icon class="size-4 animate-spin" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="size-4" />
    </template>
  </Sonner>
</template>
