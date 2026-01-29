<script setup lang="ts">
defineProps<{
  title: string;
}>();
</script>

<template>
  <div class="flex items-center gap-2">
    <h2 class="text-sm font-semibold text-muted-foreground uppercase whitespace-nowrap">
      {{ title }}
    </h2>
    <div class="h-px flex-1 bg-border" />
  </div>
</template>
