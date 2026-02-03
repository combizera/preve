<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight } from 'lucide-vue-next';

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
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import { availableColors, getColorClass } from '@/lib/category-colors';
import { availableIcons, getIconComponent } from '@/lib/category-icons';
import { cn } from '@/lib/utils';
import { update } from '@/routes/categories';
import { ICategory } from '@/types/models/category';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
  category: ICategory;
}>();

const form = useForm({
  name: props.category.name,
  type: props.category.type,
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
  <Dialog v-model:open="open">
    <form>
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Edit Category</DialogTitle>
          <DialogDescription>
            Make changes to {{ category?.name }}. Click save when you're done.
          </DialogDescription>
        </DialogHeader>

        <div class="grid grid-cols-2 gap-4">
          <!-- Type -->
          <div class="col-span-2 grid gap-3">
            <Label for="type"> Type </Label>
            <ToggleGroup v-model="form.type" class="w-full">
              <ToggleGroupItem :value="TRANSACTION_TYPE.EXPENSE" class="flex-1 gap-2">
                <ArrowUpRight :size="16" />
                Expense
              </ToggleGroupItem>
              <ToggleGroupItem :value="TRANSACTION_TYPE.INCOME" class="flex-1 gap-2">
                <ArrowDownLeft :size="16" />
                Income
              </ToggleGroupItem>
            </ToggleGroup>
            <InputError :message="form.errors.type" />
          </div>

          <div class="col-span-2 grid gap-3">
            <Label for="name"> Name </Label>
            <Input
              id="name"
              name="name"
              placeholder="Category Name"
              v-model="form.name"
            />
            <InputError :message="form.errors.name" />
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
                    'flex cursor-pointer items-center justify-center rounded border-2 p-2',
                    form.icon === icon && getColorClass(form.color, 'bg'),
                    form.icon === icon ? getColorClass(form.color, 'border') : 'hover:border-muted-foreground',
                    form.icon === icon && getColorClass(form.color, 'text'),
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
                  :class="cn('size-5', form.icon === icon ? getColorClass(form.color, 'text') : 'text-foreground')"
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
                      ? getColorClass(color, 'border')
                      : 'border-border hover:border-muted-foreground',
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
                <div :class="cn('h-full w-full rounded-xs', getColorClass(color, 'bg'))" />
              </label>
            </div>
            <InputError :message="form.errors.color" />
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button variant="outline"> Cancel </Button>
          </DialogClose>
          <Button type="button" @click="updateCategory" :disabled="form.processing">
            Save changes
          </Button>
        </DialogFooter>
      </DialogContent>
    </form>
  </Dialog>
</template>
