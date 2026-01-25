<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { availableColors, getColorClass } from '@/lib/category-colors';
import { availableIcons, getIconComponent } from '@/lib/category-icons';
import { cn } from '@/lib/utils';
import { update } from '@/routes/categories';
import { Category } from '@/types/models/category';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  category: Category;
}>();

const form = useForm({
  name: props.category.name,
  slug: props.category.slug,
  description: props.category.description ?? '',
  icon: props.category.icon,
  color: props.category.color,
});

const updateCategory = () => {
  form.submit(update(props.category.id), {
    onSuccess: () => {
      open.value = false;
    }
  });
};
</script>

<template>
  <Dialog open>
    <form>
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Edit Category</DialogTitle>
          <DialogDescription>
            Make changes to {{ category?.name }}. Click save when you're done.
          </DialogDescription>
        </DialogHeader>

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-3">
            <Label for="name"> Name </Label>
            <Input
              id="name"
              name="name"
              placeholder="Category Name"
              v-model="form.name"
            />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-3">
            <Label for="slug"> Slug </Label>
            <Input
              id="slug"
              name="slug"
              placeholder="category-slug"
              v-model="form.slug"
            />
            <InputError :message="form.errors.slug" />
          </div>

          <div class="col-span-2 grid gap-3">
            <Label for="description"> Description </Label>
            <Input
              id="description"
              name="description"
              placeholder="This is a category for..."
              v-model="form.description"
            />
            <InputError :message="form.errors.description" />
          </div>

          <div class="col-span-2 grid gap-3">
            <Label>Icon</Label>
            <div class="grid grid-cols-8 justify-start gap-2">
              <label
                v-for="icon in availableIcons"
                :key="icon"
                :class="
                  cn(
                    'flex cursor-pointer items-center justify-center rounded border border-gray-700 p-2 hover:border-primary',
                    form.icon === icon && 'border-primary bg-primary/10',
                  )
                "
              >
                <input
                  type="radio"
                  name="icon"
                  :value="icon"
                  v-model="form.icon"
                  class="sr-only"
                />
                <component
                  :is="getIconComponent(icon)"
                  class="size-5 text-gray-500"
                />
              </label>
            </div>
            <InputError :message="form.errors.icon" />
          </div>

          <div class="col-span-2 grid gap-3">
            <Label>Color</Label>
            <div class="mt-2 flex gap-2">
              <label
                v-for="color in availableColors"
                :key="color"
                :class="
                  cn(
                    'h-6 w-12 cursor-pointer rounded border-2 p-1',
                    form.color === color
                      ? 'border-gray-100'
                      : 'border-gray-800',
                  )
                "
              >
                <input
                  type="radio"
                  name="color"
                  :value="color"
                  v-model="form.color"
                  class="sr-only"
                />
                <div
                  :class="
                    cn(
                      'h-full w-full rounded-xs',
                      getColorClass(color, 'bg', 500),
                    )
                  "
                />
              </label>
            </div>
            <InputError :message="form.errors.color" />
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline" @click="open = false"> Cancel </Button>
          </DialogClose>
          <Button type="button" @click="updateCategory" :disabled="form.processing">
            Save changes
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
