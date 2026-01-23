<script setup lang="ts">
import { Trash, Edit } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
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

defineProps<{
    categories: Category[];
}>();
</script>

<template>
    <Table>
        <TableCaption>A list yours categories</TableCaption>
        <TableHeader>
            <TableRow>
                <TableHead> Name </TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Actions</TableHead>
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
                <TableCell>
                    <div class="inline-flex items-center justify-center rounded p-1.5">
                        <Button size="sm" variant="secondary" class="mr-2">
                            <Edit :size="18" />
                        </Button>
                        <Button size="sm" variant="destructive">
                            <Trash :size="18" />
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
