<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Category } from '@/types/models/category';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    category: Category | null;
}>();

const form = useForm({});

const deleteCategory = () => {
    const category = props.category;
    if (!category) return;

    form.delete(route('categories.destroy', category.id), {
        onSuccess: () => {
            open.value = false;
        },
    });
};
</script>

<template>
    <AlertDialog :open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete
                    your Category "{{ category?.name }}" and remove your data
                    from our servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="open = false">
                    Cancel
                </AlertDialogCancel>
                <AlertDialogAction @click="deleteCategory" :disabled="form.processing">
                    Remove
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
