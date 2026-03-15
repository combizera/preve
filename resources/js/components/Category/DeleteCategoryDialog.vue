<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

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
import { ICategory } from '@/types/models/category';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    category: ICategory | null;
}>();

const { t } = useI18n();
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
                <AlertDialogTitle>{{ t('generic.confirm.title') }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ t('generic.confirm.deleteCategory', { name: category?.name }) }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>
                    {{ t('generic.actions.cancel') }}
                </AlertDialogCancel>
                <AlertDialogAction variant="destructive" @click="deleteCategory" :disabled="form.processing">
                    {{ t('generic.actions.confirm') }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
