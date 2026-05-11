<script setup lang="ts">
import { PiggyBank } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import ActionGroup from '@/components/ActionGroup.vue';
import EmptyState from '@/components/EmptyState.vue';
import DeleteSavingsBucketDialog from '@/components/SavingsBucket/DeleteSavingsBucketDialog.vue';
import EditSavingsBucketDialog from '@/components/SavingsBucket/EditSavingsBucketDialog.vue';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { getColorClass } from '@/lib/accent-colors';
import { formatCentsToDisplay, getCurrencySymbol } from '@/lib/currency';
import { getSavingsBucketIconComponent } from '@/lib/savings-bucket-icons';
import { cn } from '@/lib/utils';
import { useSavingsBucketStore } from '@/stores/savings-bucket.store';
import type { ISavingsBucket } from '@/types/models/savings-bucket';

const { t } = useI18n();
const store = useSavingsBucketStore();

defineProps<{
  savingsBuckets: ISavingsBucket[];
}>();

function progressPercent(bucket: ISavingsBucket): number {
  if (bucket.target_amount <= 0) return 0;
  const ratio = (bucket.current_amount / bucket.target_amount) * 100;
  return Math.max(0, Math.min(100, Math.round(ratio)));
}
</script>

<template>
  <div class="mt-4">
    <EmptyState
      v-if="savingsBuckets.length === 0"
      :title="t('savings.empty.title')"
      :description="t('savings.empty.description')"
      :show-button="false"
    >
      <template #icon>
        <PiggyBank class="size-8 text-muted-foreground" />
      </template>
    </EmptyState>

    <Table v-else>
      <TableHeader>
        <TableRow>
          <TableHead>{{ t('generic.labels.name') }}</TableHead>
          <TableHead>{{ t('savings.table.balance') }}</TableHead>
          <TableHead class="w-[40%]">
            {{ t('savings.table.progress') }}
          </TableHead>
          <TableHead class="text-right">
            {{ t('generic.labels.actions') }}
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="bucket in savingsBuckets" :key="bucket.id">
          <TableCell class="flex items-center gap-3">
            <div
              :class="[
                getColorClass(bucket.color, 'bg'),
                getColorClass(bucket.color, 'border'),
                'inline-flex items-center justify-center rounded border p-1.5',
              ]"
            >
              <component
                :is="getSavingsBucketIconComponent(bucket.icon)"
                :size="18"
                :class="getColorClass(bucket.color, 'text')"
              />
            </div>
            <p class="font-medium">{{ bucket.name }}</p>
          </TableCell>
          <TableCell class="text-sm whitespace-nowrap">
            <span class="font-medium text-foreground">
              {{ getCurrencySymbol() }}
              {{ formatCentsToDisplay(bucket.current_amount) }}
            </span>
            <span class="text-muted-foreground">
              /
              {{ getCurrencySymbol() }}
              {{ formatCentsToDisplay(bucket.target_amount) }}
            </span>
          </TableCell>
          <TableCell>
            <div class="space-y-1.5">
              <div
                class="relative h-1.5 w-full overflow-hidden rounded-full bg-foreground/10"
              >
                <div
                  :class="
                    cn(
                      'h-full transition-all',
                      getColorClass(bucket.color, 'picker'),
                    )
                  "
                  :style="{ width: `${progressPercent(bucket)}%` }"
                />
              </div>
              <p class="text-xs text-muted-foreground">
                {{ progressPercent(bucket) }}%
              </p>
            </div>
          </TableCell>
          <TableCell class="text-right">
            <ActionGroup>
              <EditButton @click="store.openEditModal(bucket)" />
              <DeleteButton @click="store.openDeleteModal(bucket)" />
            </ActionGroup>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <EditSavingsBucketDialog />
    <DeleteSavingsBucketDialog />
  </div>
</template>
