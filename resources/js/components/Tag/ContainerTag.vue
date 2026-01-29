<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import TableTag from '@/components/Tag/TableTag.vue';
import type { ITag } from '@/types/models/tag';

defineProps<{
  tags: ITag[];
}>();
</script>

<template>
  <div class="mt-4">
    <EmptyState
      v-if="tags.length === 0"
      title="No tags yet"
      description="Start by creating your first tag"
      :showButton="false"
    />

    <TableTag :tags="tags" v-else />
  </div>
</template>
