<script setup lang="ts">
import ActionGroup from '@/components/ActionGroup.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import { useTagStore } from '@/stores/tag.store';
import { ITag } from '@/types/models/tag';

interface Props {
  tag: ITag;
}

defineProps<Props>();

const tagStore = useTagStore();
</script>

<template>
  <ActionGroup>
    <EditButton @click="tagStore.openEditModal(tag)" />
    <DeleteButton @click="tagStore.openDeleteModal(tag)" />
  </ActionGroup>
</template>
