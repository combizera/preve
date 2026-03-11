<script setup lang="ts">
defineProps<{
  title: string;
}>();
</script>

<template>
  <div class="flex items-center gap-2">
    <span class="text-sm font-semibold text-muted-foreground uppercase whitespace-nowrap">
      {{ title }}
    </span>
    <div class="h-px flex-1 bg-border" />
  </div>
</template>
