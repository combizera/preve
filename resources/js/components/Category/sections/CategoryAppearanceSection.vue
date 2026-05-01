<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import InputError from '@/components/InputError.vue';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Label } from '@/components/ui/label';
import { availableColors, getColorClass } from '@/lib/category-colors';
import { availableIcons, getIconComponent } from '@/lib/category-icons';
import { cn } from '@/lib/utils';
import type { ICategoryForm } from '@/types/models/category';

const open = defineModel<boolean>('open', { required: true });

defineProps<{
  form: InertiaForm<ICategoryForm>;
}>();

const { t } = useI18n();
</script>

<template>
  <Collapsible v-model:open="open" class="rounded-md border">
    <CollapsibleTrigger
      class="flex w-full items-center justify-between gap-2 px-4 py-3 text-sm font-medium hover:bg-muted/50 cursor-pointer"
    >
      <span>{{ t('categories.sections.appearance') }}</span>
      <ChevronDown
        :size="16"
        class="shrink-0 text-muted-foreground transition-transform"
        :class="{ 'rotate-180': open }"
      />
    </CollapsibleTrigger>

    <CollapsibleContent class="border-t px-4 py-4">
      <div class="grid gap-4">
        <!-- Icon -->
        <div class="grid gap-3">
          <Label>{{ t('generic.labels.icon') }}</Label>
          <div class="grid grid-cols-8 gap-2">
            <label
              v-for="icon in availableIcons"
              :key="icon"
              :class="
                cn(
                  'flex cursor-pointer items-center justify-center rounded border-2 p-2',
                  form.icon === icon && getColorClass(form.color, 'bg'),
                  form.icon === icon
                    ? getColorClass(form.color, 'border')
                    : 'hover:border-muted-foreground',
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
                :class="
                  cn(
                    'size-5',
                    form.icon === icon
                      ? getColorClass(form.color, 'text')
                      : 'text-foreground',
                  )
                "
              />
            </label>
          </div>
          <InputError :message="form.errors.icon" />
        </div>

        <!-- Color -->
        <div class="grid gap-3">
          <Label>{{ t('generic.labels.color') }}</Label>
          <div class="grid grid-cols-8 gap-2">
            <label
              v-for="color in availableColors"
              :key="color"
              :class="
                cn(
                  'group h-8 w-12 cursor-pointer rounded border-2 p-1',
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
              <div
                :class="
                  cn(
                    'h-full w-full rounded-xs group-hover:brightness-90',
                    getColorClass(color, 'picker'),
                  )
                "
              />
            </label>
          </div>
          <InputError :message="form.errors.color" />
        </div>
      </div>
    </CollapsibleContent>
  </Collapsible>
</template>
