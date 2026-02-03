<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import QuickCreateCard from '@/components/QuickCreateCard.vue';
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
import { TRANSACTION_TYPE } from '@/enums/transaction-type';
import { availableColors, getColorClass } from '@/lib/category-colors';
import { availableIcons, getIconComponent } from '@/lib/category-icons';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
import { store } from '@/routes/categories';

const types = TRANSACTION_TYPE;

const form = useForm({
  name: '',
  description: '',
  type: 'expense',
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
  <QuickCreateCard title="New Category">
    <form
      class="flex w-full flex-wrap items-start gap-3"
      @submit.prevent="createCategory"
    >
      <div class="flex min-w-1/4 flex-col justify-start gap-2">
        <Label for="name">Name</Label>
        <Input id="name" name="name" placeholder="Name" v-model="form.name" />
        <InputError :message="form.errors.name" />
      </div>

      <div class="flex min-w-1/4 flex-1 flex-col justify-start gap-2">
        <Label for="description">Description</Label>
        <Input
          id="description"
          name="description"
          placeholder="Describe your category"
          v-model="form.description"
        />
        <InputError :message="form.errors.description" />
      </div>

      <div class="flex flex-col justify-start gap-2">
        <Label for="type">Type</Label>
        <Select v-model="form.type">
          <SelectTrigger class="w-full min-w-[150px]">
            <SelectValue placeholder="Select a type" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Type</SelectLabel>
              <SelectItem v-for="type in types" :value="type" :key="type">
                {{ capitalizeFirstLetter(type) }}
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.type" />
      </div>

      <div class="flex flex-col justify-start gap-2">
        <Label for="color">Color</Label>
        <Select v-model="form.color">
          <SelectTrigger class="w-full min-w-[150px]">
            <SelectValue placeholder="Select a color">
              <div v-if="form.color" class="flex items-center gap-2">
                <div :class="cn('h-4 w-4 rounded', getColorClass(form.color, 'bg'))" />
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
                <div :class="cn('h-4 w-4 rounded', getColorClass(color, 'bg'))" />
                <span>{{ capitalizeFirstLetter(color) }}</span>
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.color" />
      </div>

      <div class="flex flex-col justify-start gap-2">
        <Label for="icon">Icon</Label>
        <Select v-model="form.icon">
          <SelectTrigger
            :class="cn('w-full min-w-[150px]', form.icon && 'pl-1')"
          >
            <SelectValue placeholder="Select an icon">
              <div
                v-if="form.icon"
                :class="
                  cn(
                    'flex size-7 items-center justify-center rounded border p-1',
                    form.color && getColorClass(form.color, 'bg'),
                    form.color && getColorClass(form.color, 'border'),
                  )
                "
              >
                <component
                  :is="getIconComponent(form.icon)"
                  :class="cn('size-4', form.color && getColorClass(form.color, 'text'))"
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
                  class="flex items-center justify-center gap-2 p-2 [&>span]:hidden"
                >
                  <component
                    :is="getIconComponent(icon)"
                    class="size-5 text-foreground"
                  />
                </SelectItem>
              </div>
            </SelectGroup>
          </SelectContent>
        </Select>
        <InputError :message="form.errors.icon" />
      </div>

      <div class="ml-2 flex h-full items-start pt-5.5">
        <Button type="submit" :disabled="form.processing" class="h-9">
          Create
        </Button>
      </div>
    </form>
  </QuickCreateCard>
</template>

<style scoped></style>
