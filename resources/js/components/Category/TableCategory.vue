<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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
                <TableHead class="w-[100px]"> Name </TableHead>
                <TableHead>Slug</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Color</TableHead>
                <TableHead>Icon</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="category in categories" :key="category.id">
                <TableCell>{{ category.name }}</TableCell>
                <TableCell>{{ category.slug }}</TableCell>
                <TableCell>{{ category.description }}</TableCell>
                <TableCell>{{ category.color }}</TableCell>
                <TableCell>{{ category.icon }}</TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
