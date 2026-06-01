<script setup lang="ts">
import { cn } from "@/lib/utils";
import type { HTMLAttributes } from "vue";

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div class="double-border bg-sidebar">
    <div data-slot="table-container" class="relative w-full overflow-auto rounded-lg border bg-background">
      <table data-slot="table" :class="cn('w-full caption-bottom text-sm', props.class)">
        <slot />
      </table>
    </div>
  </div>
</template>
