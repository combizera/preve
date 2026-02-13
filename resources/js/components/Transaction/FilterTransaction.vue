<script setup lang="ts">
import { Filter } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger, SheetFooter, SheetClose } from '@/components/ui/sheet';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { useFilter } from '@/composables/useFilter';
import type { ITransactionFilters } from '@/types/filters';

interface Props {
    filters: ITransactionFilters;
    categories: Array<{ id: number; name: string }>;
    tags: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const defaultFilters: ITransactionFilters = {
    search: '',
    type: '',
    category_id: '',
    date_start: '',
    date_end: '',
    tags: [],
};

const { form, activeCount, apply, clear } = useFilter<ITransactionFilters>(
    'transactions.index',
    { ...defaultFilters, ...props.filters },
    defaultFilters
);
</script>

<template>
    <Sheet>
        <SheetTrigger as-child>
            <Button variant="outline" class="relative">
                <Filter class="w-4 h-4 mr-2" />
                Filters
                <Badge v-if="activeCount > 0" variant="secondary"
                    class="ml-2 px-1.5 h-5 min-w-5 flex items-center justify-center rounded-full">
                    {{ activeCount }}
                </Badge>
            </Button>
        </SheetTrigger>
        <SheetContent class="flex h-full flex-col bg-background sm:max-w-xl">
            <SheetHeader class="border-b border-border px-6 py-4">
                <SheetTitle>Filter Transactions</SheetTitle>
                <SheetDescription>
                    Refine your search by date range, category, or tags.
                </SheetDescription>
            </SheetHeader>

            <div class="flex-1 overflow-y-auto">
                <div class="space-y-6 p-6">
                    <section name="search-filter">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <Input id="search" v-model="form.search" placeholder="Search by description..." />
                        </div>
                    </section>

                    <section name="type-category-tag-filter">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="type">Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger id="type" class="w-full">
                                        <SelectValue placeholder="All types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All types</SelectItem>
                                        <SelectItem value="income">Income</SelectItem>
                                        <SelectItem value="expense">Expense</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="category">Category</Label>
                                <Select v-model="form.category_id">
                                    <SelectTrigger id="category" class="w-full">
                                        <SelectValue placeholder="Select a category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All categories</SelectItem>
                                        <SelectItem v-for="category in categories" :key="category.id"
                                            :value="String(category.id)">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="tag">tag</Label>
                                <Select v-model="form.tags" multiple>
                                    <SelectTrigger id="tag" class="w-full">
                                        <SelectValue placeholder="Select a tag(s)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All tags</SelectItem>
                                        <SelectItem v-for="tag in tags" :key="tag.id" :value="String(tag.id)">
                                            {{ tag.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </section>

                    <section name="date-range-filter">
                        <div class="space-y-4 rounded-lg border border-border bg-muted/50 p-4">
                            <p class="text-sm font-medium text-foreground">Filter by date
                                range</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="date_start">Start Date</Label>
                                    <Input id="date_start" v-model="form.date_start" type="date" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="date_end">End Date</Label>
                                    <Input id="date_end" v-model="form.date_end" type="date" />
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <SheetFooter class="flex-col-reverse gap-2 border-t border-border p-6 sm:flex-row sm:justify-end sm:gap-3">
                <Button variant="ghost" @click="clear" :disabled="form.processing">
                    Clear Filters
                </Button>

                <SheetClose as-child>
                    <Button type="submit" @click="apply" :disabled="form.processing">
                        <span v-if="form.processing">Filtering...</span>
                        <span v-else>Apply Filters</span>
                    </Button>
                </SheetClose>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>