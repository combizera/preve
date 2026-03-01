<script setup lang="ts">
interface Props {
  title: string;
  description?: string;
  variant?: 'default' | 'small';
  hasActions?: boolean;
}

withDefaults(defineProps<Props>(), {
  variant: 'default',
  hasActions: false,
});
</script>

<template>
  <header
    :class="
      variant === 'small' ? '' : 'my-4 flex items-center justify-between'
    "
  >
    <div class="space-y-0.5">
      <h2
        :class="
          variant === 'small'
            ? 'mb-0.5 text-base font-medium'
            : 'text-xl font-semibold tracking-tight'
        "
      >
        {{ title }}
      </h2>
      <p v-if="description" class="text-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>

    <div v-if="hasActions">
      <slot />
    </div>
  </header>
</template>
