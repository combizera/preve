<script setup lang="ts">
import { ref } from 'vue';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteCategoryDialog from '@/components/Category/DeleteCategoryDialog.vue';
import EditCategoryDialog from '@/components/Category/EditCategoryDialog.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { CategoryColor, getColorClass } from '@/lib/category-colors';
import { getIconComponent } from '@/lib/category-icons';
import { cn } from '@/lib/utils';
import { ICategory } from '@/types/models/category';

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedCategory = ref<ICategory | null>(null);

const openEditDialog = (category: ICategory) => {
  selectedCategory.value = category;
  showEditDialog.value = true;
};

const openDeleteDialog = (category: ICategory) => {
  selectedCategory.value = category;
  showDeleteDialog.value = true;
};

interface Props {
  categories: ICategory[];
  type?: 'income' | 'expense';
}

withDefaults(defineProps<Props>(), {
  type: 'expense',
});
</script>

<template>
  <div>
    <div class="mb-2 flex w-full items-center gap-2 px-2">
      <i
        :class="
          cn(
            'size-4 rounded',
            type === 'expense'
              ? getColorClass(CategoryColor.Red, 'bg')
              : getColorClass(CategoryColor.Green, 'bg'),
          )
        "
      />
      <p class="text-foreground">
        {{ type === 'income' ? 'Income' : 'Expense' }}
      </p>
      <div class="ml-1 h-px flex-1 bg-border"></div>
    </div>
    <Table>
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
                getColorClass(category.color, 'bg'),
                getColorClass(category.color, 'border'),
                'inline-flex items-center justify-center rounded border p-1.5',
              ]"
            >
              <component
                :is="getIconComponent(category.icon)"
                :size="18"
                :class="getColorClass(category.color, 'text')"
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
              <EditButton @click="openEditDialog(category)" />

              <DeleteButton @click="openDeleteDialog(category)" />
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
  </div>
</template>
