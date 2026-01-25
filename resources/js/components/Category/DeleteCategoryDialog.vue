<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

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
import { destroy } from '@/routes/categories';
import { Category } from '@/types/models/category';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    category: Category | null;
}>();

const form = useForm({});

const deleteCategory = () => {
    const category = props.category;
    if (!category) return;

    form.submit(destroy(category.id), {
        onSuccess: () => {
            open.value = false;
        }
    });
};
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete
                    the category "{{ category?.name }}".
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>
                    Cancel
                </AlertDialogCancel>
                <AlertDialogAction @click="deleteCategory" :disabled="form.processing">
                    Confirm
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
