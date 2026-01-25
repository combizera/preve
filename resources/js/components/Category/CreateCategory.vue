<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { availableColors, getColorClass } from '@/lib/category-colors';
import { availableIcons, getIconComponent } from '@/lib/category-icons';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { store } from '@/routes/categories';

const form = useForm({
  name: '',
  slug: '',
  description: '',
  color: '',
  icon: '',
});

const createCategory = () => {
  form.submit(store(), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <div class="flex flex-col gap-4 rounded-md bg-sidebar p-6">
    <div class="flex items-center gap-2">
      <Plus :size="18" class="text-muted-foreground" />
      <p class="text-sm text-muted-foreground">New Category</p>
    </div>

    <form class="flex w-full flex-wrap items-end gap-3" @submit.prevent="createCategory">
      <div class="flex flex-col gap-2 justify-start h-full">
        <Label for="name">Name</Label>
        <Input
          id="name"
          name="name"
          placeholder="Name"
          v-model="form.name"
        />
        <InputError :message="form.errors.name" />
      </div>

      <div class="flex flex-col gap-2 justify-start h-full">
        <!-- // TODO: Conforme o usuário for digitando o nome, preencher automaticamente o slug -->
        <Label for="slug">Slug</Label>
        <Input
          id="slug"
          name="slug"
          placeholder="category-slug"
          v-model="form.slug"
        />
        <InputError :message="form.errors.slug" />
      </div>

      <div class="flex flex-col gap-2 flex-1 justify-start h-full">
        <Label for="description">Description</Label>
        <Input
          id="description"
          name="description"
          placeholder="Describe your category"
          v-model="form.description"
        />
        <InputError :message="form.errors.description" />
      </div>

      <div class="flex flex-col gap-2 justify-start h-full">
        <Label for="color">Color</Label>
        <Select v-model="form.color">
          <SelectTrigger class="w-full min-w-[150px]">
            <SelectValue placeholder="Select a color">
              <div v-if="form.color" class="flex items-center gap-2">
                <div :class="cn('h-4 w-4 rounded', getColorClass(form.color, 'bg', 500))" />
                <span>{{ capitalizeFirstLetter(form.color) }}</span>
              </div>
            </SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Color</SelectLabel>
              <SelectItem
                v-for="color in availableColors"
                :value="color"
                :key="color"
              >
                <div :class="cn('h-4 w-4 rounded', getColorClass(color, 'bg', 500))" />
                <span>{{ capitalizeFirstLetter(color) }}</span>
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.color" />
      </div>

      <div class="flex flex-col gap-2 justify-start h-full">
        <Label for="icon">Icon</Label>
        <Select v-model="form.icon">
          <SelectTrigger :class="cn('w-full min-w-[150px]', form.icon && 'pl-1')">
            <SelectValue placeholder="Select an icon">
              <div v-if="form.icon" :class="cn('flex items-center justify-center size-7 p-1 rounded border', getColorClass(form.color, 'bg', 950), getColorClass(form.color, 'border', 900))">
                <component
                  :is="getIconComponent(form.icon)"
                  :class="cn('size-4', getColorClass(form.color, 'text', 300))"
                />
              </div>
            </SelectValue>
          </SelectTrigger>
          <SelectContent class="min-w-[150px]">
            <SelectGroup>
              <SelectLabel>Icon</SelectLabel>
              <div class="grid grid-cols-5 gap-1">
                <SelectItem
                  v-for="icon in availableIcons"
                  :value="icon"
                  :key="icon"
                  class="flex items-center justify-center p-2 gap-2 [&>span]:hidden"
                >
                  <component
                    :is="getIconComponent(icon)"
                    class="size-5 text-gray-200"
                  />
                </SelectItem>
              </div>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.icon" />
      </div>

      <div class="h-full flex items-start pt-5.5 ml-2">
        <Button type="submit" :disabled="form.processing" class="h-9">
          Create
        </Button>
      </div>
    </form>
  </div>
</template>

<style scoped></style>
