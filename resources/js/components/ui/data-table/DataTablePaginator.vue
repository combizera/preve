<script setup lang="ts" generic="TData">
import Button from '@/components/ui/button/Button.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type IPaginate } from '@/types/models/paginated';
import { paginationNavigate } from '@/utils/navigate';

import { ChevronLeftIcon, ChevronRightIcon, ChevronsLeftIcon, ChevronsRightIcon } from 'lucide-vue-next';
import { AcceptableValue } from 'reka-ui';
import { computed } from 'vue';

const props = defineProps<{
  pagination: IPaginate<TData>
}>()

const currentPage = computed(() => props.pagination.current_page)
const totalPages = computed(() => props.pagination.last_page)
const pageSize = computed(() => props.pagination.per_page)

function handlePageSizeChange(value: AcceptableValue) {
  paginationNavigate({
    page: 1,
    per_page: Number(value),
  })
}

function handlePageChange(page: number) {
  paginationNavigate({ page })
}
</script>

<template>
  <div class="flex items-center justify-between px-2">
    <div class="flex-1 text-sm text-muted-foreground">
      Page {{ currentPage }} of {{ totalPages }}
    </div>
    <div class="flex items-center space-x-6 lg:space-x-8">
      <div class="flex items-center space-x-2">
        <p class="text-sm font-medium">
          Rows per page
        </p>
        <Select :model-value="`${pageSize}`" @update:model-value="handlePageSizeChange">
          <SelectTrigger class="h-8 w-[70px]">
            <SelectValue :placeholder="`${pageSize}`" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
              {{ pageSize }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          class="hidden size-8 p-0 lg:flex"
          :disabled="currentPage === 1"
          @click="handlePageChange(1)"
        >
          <span class="sr-only">Go to first page</span>
          <ChevronsLeftIcon class="size-4" />
        </Button>
        <Button
          variant="outline"
          class="size-8 p-0"
          :disabled="!pagination.prev_page_url"
          @click="handlePageChange(currentPage - 1)"
        >
          <span class="sr-only">Go to previous page</span>
          <ChevronLeftIcon class="size-4" />
        </Button>
        <Button
          variant="outline"
          class="size-8 p-0"
          :disabled="!pagination.next_page_url"
          @click="handlePageChange(currentPage + 1)"
        >
          <span class="sr-only">Go to next page</span>
          <ChevronRightIcon class="size-4" />
        </Button>
        <Button
          variant="outline"
          class="hidden size-8 p-0 lg:flex"
          :disabled="currentPage >= totalPages"
          @click="handlePageChange(totalPages)"
        >
          <span class="sr-only">Go to last page</span>
          <ChevronsRightIcon class="size-4" />
        </Button>
      </div>
    </div>
  </div>
</template>