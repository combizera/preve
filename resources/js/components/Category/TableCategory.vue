<script setup lang="ts">
import { ref } from 'vue';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteCategoryDialog from '@/components/Category/DeleteCategoryDialog.vue';
import EditCategoryDialog from '@/components/Category/EditCategoryDialog.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { getColorClass } from '@/lib/category-colors';
import { getIconComponent } from '@/lib/category-icons';
import { Category } from '@/types/models/category';

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedCategory = ref<Category | null>(null);

const openEditDialog = (category: Category) => {
  selectedCategory.value = category;
  showEditDialog.value = true;
};

const openDeleteDialog = (category: Category) => {
  selectedCategory.value = category;
  showDeleteDialog.value = true;
};

defineProps<{
  categories: Category[];
}>();
</script>

<template>
  <Table>
    <TableCaption>Your Categories Income</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead>Name</TableHead>
        <TableHead>Description</TableHead>
        <TableHead class="text-right">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="category in categories" :key="category.id">
        <TableCell class="flex items-center gap-3">
          <div
            :class="[
              getColorClass(category.color, 'bg', 950),
              'inline-flex items-center justify-center rounded p-1.5',
            ]"
          >
            <component
              :is="getIconComponent(category.icon)"
              :size="18"
              :class="getColorClass(category.color, 'text', 500)"
            />
          </div>
          <p class="font-medium">
            {{ category.name }}
          </p>
        </TableCell>
        <TableCell>
          <p class="text-sm text-muted-foreground">
            {{
              category.description && category.description.length > 25
                ? category.description.slice(0, 25) + '...'
                : category.description
            }}
          </p>
        </TableCell>
        <TableCell class="text-right">
          <ActionGroup>
            <EditButton 
              @click="openEditDialog(category)"
            />

            <DeleteButton 
              @click="openDeleteDialog(category)"
            />
          </ActionGroup>
        </TableCell>
      </TableRow>
    </TableBody>

    <EditCategoryDialog
      v-if="showEditDialog && selectedCategory"
      v-model:open="showEditDialog"
      :category="selectedCategory"
    />

    <DeleteCategoryDialog
      v-model:open="showDeleteDialog"
      :category="selectedCategory"
    />
  </Table>
</template>
