<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import type { ITag } from '@/types/models/tag';

interface Props {
  tags: ITag[];
  placeholder?: string;
  id?: string;
}

const props = withDefaults(defineProps<Props>(), {
  id: 'tags',
  placeholder: undefined,
});

const modelValue = defineModel<number[]>({ required: true });

const { t } = useI18n();

const stringValue = computed({
  get: () => modelValue.value.map(String),
  set: (value: string[]) => {
    modelValue.value = value.map(Number);
  },
});

const tagNameById = computed(
  () => new Map(props.tags.map((tag) => [tag.id, tag.name])),
);

const selectedNames = computed(() =>
  modelValue.value
    .map((id) => tagNameById.value.get(id))
    .filter((name): name is string => Boolean(name))
    .join(', '),
);

const placeholderText = computed(
  () => props.placeholder ?? t('generic.placeholders.selectTags'),
);
</script>

<template>
  <Select v-model="stringValue" multiple>
    <SelectTrigger :id="id" class="w-full">
      <span v-if="selectedNames" class="truncate text-left">
        {{ selectedNames }}
      </span>
      <SelectValue v-else :placeholder="placeholderText" />
    </SelectTrigger>
    <SelectContent>
      <SelectGroup>
        <SelectLabel>{{ t('models.tag.name') }}</SelectLabel>
        <SelectItem v-for="tag in tags" :key="tag.id" :value="String(tag.id)">
          {{ tag.name }}
        </SelectItem>
      </SelectGroup>
    </SelectContent>
  </Select>
</template>
