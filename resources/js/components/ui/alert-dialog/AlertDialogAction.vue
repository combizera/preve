<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button'
import { cn } from "@/lib/utils"
import { reactiveOmit } from "@vueuse/core"
import { VariantProps } from 'class-variance-authority'
import type { AlertDialogActionProps } from "reka-ui"
import { AlertDialogAction } from "reka-ui"
import type { HTMLAttributes } from "vue"

const props = defineProps<AlertDialogActionProps & {
  class?: HTMLAttributes["class"],
  variant?: VariantProps<typeof buttonVariants>['variant']
}>()

const delegatedProps = reactiveOmit(props, "class")
</script>

<template>
  <AlertDialogAction v-bind="delegatedProps" :class="cn(buttonVariants({ variant: props.variant }), props.class)">
    <slot />
  </AlertDialogAction>
</template>
