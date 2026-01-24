<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { route } from 'ziggy-js';

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
import { availableColors } from '@/lib/category-colors';
import { availableIcons, getIconComponent } from '@/lib/category-icons';
import { capitalizeFirstLetter } from '@/lib/utils';
</script>

<template>
  <div class="flex flex-col gap-4 rounded-md bg-sidebar p-4 px-6">
    <div class="flex items-center gap-1">
      <Plus :size="18" class="text-muted-foreground" />
      <p class="text-sm text-muted-foreground">New Category</p>
    </div>

    <Form
      class="flex items-end gap-2"
      :action="route('categories.store')"
      method="POST"
    >
      <div class="grid gap-1">
        <Label for="name"> Name * </Label>
        <Input id="name" name="name" placeholder="Category Name" />
      </div>

      <div class="grid gap-1">
        <!-- // TODO: Conforme o usuário for digitando o nome, preencher automaticamente o slug -->
        <Label for="slug"> Slug </Label>
        <Input id="slug" name="slug" placeholder="category-slug" />
      </div>

      <div class="grid w-[400px] gap-1">
        <Label for="description"> Descritpion </Label>
        <Input
          id="description"
          name="description"
          placeholder="Your description"
        />
      </div>

      <div class="grid gap-1">
        <Label for="color"> Color </Label>
        <Select name="color">
          <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Select a color" />
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
      </div>

      <div class="grid gap-1">
        <Label for="icon"> Icon </Label>

        <Select name="icon">
          <SelectTrigger class="w-[150px]">
            <SelectValue placeholder="Select a icon" />
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
      </div>

      <Button type="submit"> Create </Button>
    </Form>
  </div>
</template>

<style scoped></style>
