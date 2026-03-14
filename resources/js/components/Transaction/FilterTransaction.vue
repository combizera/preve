<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
  SheetFooter,
  SheetClose,
} from '@/components/ui/sheet';
import { useFilter } from '@/composables/useFilter';
import transactionRoutes from '@/routes/transactions';
import type { ITransactionFilters } from '@/types/filters';

interface Props {
  filters: ITransactionFilters;
  categories: Array<{ id: number; name: string }>;
  tags: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const { t } = useI18n();

const defaultFilters: ITransactionFilters = {
  search: '',
  type: '',
  date_start: '',
  date_end: '',
  categories: [],
  tags: [],
};

const { form, activeCount, apply, clear } = useFilter<ITransactionFilters>(
  transactionRoutes.index().url,
  { ...defaultFilters, ...props.filters },
  defaultFilters,
);
</script>

<template>
  <Sheet>
    <SheetTrigger as-child>
      <Button variant="outline" class="relative">
        <Filter class="mr-2 h-4 w-4" />
        {{ t('generic.actions.filter') }}
        <Badge
          v-if="activeCount > 0"
          variant="secondary"
          class="ml-2 flex h-5 min-w-5 items-center justify-center rounded-full px-1.5"
        >
          {{ activeCount }}
        </Badge>
      </Button>
    </SheetTrigger>
    <SheetContent
      class="flex h-full w-screen translate-x-1 flex-col bg-background sm:max-w-2xl sm:translate-x-0"
    >
      <SheetHeader class="border-b border-border px-6 py-4">
        <SheetTitle>{{ t('transactions.filter.title') }}</SheetTitle>
        <SheetDescription>
          {{ t('transactions.filter.description') }}
        </SheetDescription>
      </SheetHeader>

      <div class="flex-1 overflow-y-auto">
        <div class="space-y-6 p-6">
          <section data-name="search-filter">
            <div class="space-y-2">
              <Label for="search">{{ t('generic.labels.search') }}</Label>
              <Input
                id="search"
                v-model="form.search"
                :placeholder="t('generic.placeholders.searchByDescription')"
              />
            </div>
          </section>

          <section data-name="type-category-tag-filter">
            <div class="grid grid-cols-3 gap-4">
              <div class="space-y-2">
                <Label for="type">{{ t('models.transaction.type') }}</Label>
                <Select v-model="form.type">
                  <SelectTrigger id="type" class="w-full">
                    <SelectValue :placeholder="t('generic.labels.allTypes')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">{{ t('generic.labels.allTypes') }}</SelectItem>
                    <SelectItem value="income">{{ t('models.transaction.income') }}</SelectItem>
                    <SelectItem value="expense">{{ t('models.transaction.expense') }}</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label for="category">{{ t('models.category.name') }}</Label>
                <Select v-model="form.categories" multiple>
                  <SelectTrigger id="category" class="w-full">
                    <SelectValue :placeholder="t('generic.placeholders.selectCategories')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">{{ t('generic.labels.allCategories') }}</SelectItem>
                    <SelectItem
                      v-for="category in categories"
                      :key="category.id"
                      :value="String(category.id)"
                    >
                      {{ category.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label for="tag">{{ t('models.tag.name') }}</Label>
                <Select v-model="form.tags" multiple>
                  <SelectTrigger id="tag" class="w-full">
                    <SelectValue :placeholder="t('generic.placeholders.selectTags')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">{{ t('generic.labels.allTags') }}</SelectItem>
                    <SelectItem
                      v-for="tag in tags"
                      :key="tag.id"
                      :value="String(tag.id)"
                    >
                      {{ tag.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </section>

          <section data-name="date-range-filter">
            <div
              class="space-y-4 rounded-lg border border-border bg-muted/50 p-4"
            >
              <p class="text-sm font-medium text-foreground">
                {{ t('transactions.filter.dateRange') }}
              </p>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="date_start">{{ t('generic.labels.startDate') }}</Label>
                  <DatePicker
                    id="date_start"
                    v-model="form.date_start"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="date_end">{{ t('generic.labels.endDate') }}</Label>
                  <Input id="date_end" v-model="form.date_end" type="date" />
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>

      <SheetFooter
        class="flex-col-reverse gap-2 border-t border-border p-6 sm:flex-row sm:justify-end sm:gap-3"
      >
        <Button variant="ghost" @click="clear" :disabled="form.processing">
          {{ t('generic.actions.clearFilters') }}
        </Button>

        <SheetClose as-child>
          <Button type="submit" @click="apply" :disabled="form.processing">
            <span v-if="form.processing">{{ t('generic.actions.filtering') }}</span>
            <span v-else>{{ t('generic.actions.applyFilters') }}</span>
          </Button>
        </SheetClose>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
