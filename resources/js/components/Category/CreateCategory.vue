<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { route } from 'ziggy-js';

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

const form = useForm({
  name: '',
  slug: '',
  description: '',
  color: '',
  icon: '',
});

const createCategory = () => {
  form.post(route('categories.store'), {
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>

<template>
  <div class="flex flex-col gap-4 rounded-md bg-sidebar p-4 px-6">
    <div class="flex items-center gap-1">
      <Plus :size="18" class="text-muted-foreground" />
      <p class="text-sm text-muted-foreground">New Category</p>
    </div>

    <form class="flex w-full flex-wrap items-end gap-3" @submit.prevent="createCategory">
      <div class="grid min-w-[200px] flex-1 gap-1">
        <Label for="name"> Name * </Label>
        <Input
          id="name"
          name="name"
          placeholder="Category Name"
          v-model="form.name"
        />
        <InputError :message="form.errors.name" />
      </div>

      <div class="grid min-w-[200px] flex-1 gap-1">
        <!-- // TODO: Conforme o usuário for digitando o nome, preencher automaticamente o slug -->
        <Label for="slug"> Slug </Label>
        <Input
          id="slug"
          name="slug"
          placeholder="category-slug"
          v-model="form.slug"
        />
        <InputError :message="form.errors.slug" />
      </div>

      <div class="grid min-w-[300px] flex-[2] gap-1">
        <Label for="description"> Description </Label>
        <Input
          id="description"
          name="description"
          placeholder="Your description"
          v-model="form.description"
        />
        <InputError :message="form.errors.description" />
      </div>

      <div class="grid min-w-[150px] gap-1">
        <Label for="color"> Color </Label>
        <Select v-model="form.color">
          <SelectTrigger>
            <SelectValue placeholder="Select a color">
              <div v-if="form.color" class="flex items-center gap-2">
                <div
                  :class="
                    cn('h-4 w-4 rounded', getColorClass(form.color, 'bg', 500))
                  "
                />
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
                {{ capitalizeFirstLetter(color) }}
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.color" />
      </div>

      <div class="grid min-w-[150px] gap-1">
        <Label for="icon"> Icon </Label>
        <Select v-model="form.icon">
          <SelectTrigger>
            <SelectValue placeholder="Select a icon">
              <component
                v-if="form.icon"
                :is="getIconComponent(form.icon)"
                class="size-5 text-gray-500"
              />
            </SelectValue>
          </SelectTrigger>
          <SelectContent class="w-[50px]">
            <SelectGroup>
              <SelectLabel>Icon</SelectLabel>
              <SelectItem
                v-for="icon in availableIcons"
                :value="icon"
                :key="icon"
                class="flex items-center justify-center"
              >
                <component
                  :is="getIconComponent(icon)"
                  class="size-5 text-gray-500"
                />
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.icon" />
      </div>

      <Button type="submit" :disabled="form.processing" class="min-w-[100px]">
        Create
      </Button>
    </form>
  </div>
</template>

<style scoped></style>
