<script setup lang="ts">
import {
  Calendar,
  ChevronDown,
  Repeat,
  CalendarSync,
  Tag as TagIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteRecurringDialog from '@/components/Recurring/DeleteRecurringDialog.vue';
import EditRecurringDialog from '@/components/Recurring/EditRecurringDialog.vue';
import { Badge } from '@/components/ui/badge';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import ToggleActiveButton from '@/components/ui/button/ToggleActiveButton.vue';
import { Card } from '@/components/ui/card';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay } from '@/lib/currency';
import {
  calculateAnnualAmount,
  calculateNextOccurrence,
  formatActivePeriod,
  formatFrequency,
} from '@/lib/recurring';
import { cn } from '@/lib/utils';
import { type ICategory } from '@/types/models/category';
import { IRecurringTransaction } from '@/types/models/recurring-transaction';
import { type ITag } from '@/types/models/tag';

const props = defineProps<{
  recurringTransaction: IRecurringTransaction;
  categories: ICategory[];
  tags: ITag[];
}>();

const isExpanded = ref(false);

const formattedAmount = computed(() =>
  formatCentsToDisplay(props.recurringTransaction.amount),
);

const categoryIcon = computed(() =>
  getIconComponent(props.recurringTransaction.category?.icon ?? null),
);

const amountClass = computed(() =>
  cn(
    'text-sm font-medium',
    props.recurringTransaction.type === 'expense'
      ? "text-foreground before:content-['-']"
      : 'text-positive',
  ),
);

const frequencyText = computed(() =>
  formatFrequency(
    props.recurringTransaction.frequency,
    props.recurringTransaction.day_of_month,
  ),
);

const nextOccurrence = computed(() =>
  calculateNextOccurrence(
    props.recurringTransaction.frequency,
    props.recurringTransaction.day_of_month,
  ),
);

const activePeriod = computed(() =>
  formatActivePeriod(
    props.recurringTransaction.start_date,
    props.recurringTransaction.end_date,
  ),
);

const annualAmount = computed(() => {
  const annual = calculateAnnualAmount(
    props.recurringTransaction.frequency,
    props.recurringTransaction.amount,
  );
  return formatCentsToDisplay(annual);
});

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value;
};

const showDeleteDialog = ref(false);
const showEditDialog = ref(false);
const selectedRecurring = ref<IRecurringTransaction | null>(null);

const openEditDialog = (recurringTransaction: IRecurringTransaction) => {
  selectedRecurring.value = recurringTransaction;
  showEditDialog.value = true;
};

const openDeleteDialog = (recurringTransaction: IRecurringTransaction) => {
  selectedRecurring.value = recurringTransaction;
  showDeleteDialog.value = true;
};
</script>

<template>
  <Card
    :class="
      cn(
        'gap-2 overflow-hidden rounded-md bg-sidebar py-2 transition-colors',
        !recurringTransaction.is_active && 'opacity-60',
      )
    "
  >
    <!-- Main Content - Always Visible -->
    <div
      class="cursor-pointer p-3 py-2 hover:bg-sidebar/80"
      @click="toggleExpand"
    >
      <div class="flex items-center justify-between gap-3">
        <!-- Left Section: Main Info -->
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <h3
              class="truncate text-sm leading-tight font-semibold text-foreground"
            >
              {{ recurringTransaction.description }}
            </h3>
            <Badge
              v-if="!recurringTransaction.is_active"
              variant="secondary"
              class="text-xs px-1.5 py-0"
            >
              Inactive
            </Badge>
          </div>
          <div class="mt-1.5 flex items-center gap-2">
            <component
              :is="categoryIcon"
              :size="14"
              class="flex-shrink-0 text-muted-foreground"
            />
            <span class="truncate text-xs font-medium text-muted-foreground">
              {{ recurringTransaction.category?.name }}
            </span>
            <span class="text-xs text-muted-foreground">•</span>
            <div class="flex items-center gap-1 text-xs text-muted-foreground">
              <Repeat :size="12" />
              <span class="font-medium">{{ frequencyText }}</span>
            </div>
          </div>
        </div>

        <!-- Right Section: Amount & Actions -->
        <div class="flex flex-shrink-0 items-center gap-1" @click.stop>
          <span :class="amountClass">R$ {{ formattedAmount }}</span>
          <ActionGroup>
            <EditButton @click="openEditDialog(recurringTransaction)" />
            <ToggleActiveButton
              :id="recurringTransaction.id!"
              :is-active="recurringTransaction.is_active"
            />
            <DeleteButton @click="openDeleteDialog(recurringTransaction)" />
          </ActionGroup>
          <ChevronDown
            :size="16"
            class="text-muted-foreground transition-transform"
            :class="{ 'rotate-180': isExpanded }"
            @click="toggleExpand"
          />
        </div>
      </div>
    </div>

    <!-- Expanded Content -->
    <div
      v-if="isExpanded"
      class="border-t border-border bg-sidebar/50 px-4 py-2 pt-3"
    >
      <div class="space-y-3">
        <!-- Details Grid -->
        <div class="grid grid-cols-3 gap-3 text-xs">
          <div class="space-y-1">
            <div class="font-medium text-muted-foreground/70">
              Annual Amount
            </div>
            <div
              :class="
                cn(
                  'flex items-center gap-1 font-semibold',
                  recurringTransaction.type === 'expense'
                    ? 'text-destructive'
                    : 'text-positive',
                )
              "
            >
              <CalendarSync :size="14" />
              <span>
                R$ {{ annualAmount }}
              </span>
            </div>
          </div>

          <div class="space-y-1">
            <div class="font-medium text-muted-foreground/70">
              Next Occurrence
            </div>
            <div class="flex items-center gap-1.5 text-foreground">
              <Calendar :size="12" />
              <span>{{ nextOccurrence }}</span>
            </div>
          </div>

          <div class="space-y-1">
            <div class="font-medium text-muted-foreground/70">
              Active Period
            </div>
            <div class="flex items-center gap-1.5 text-foreground">
              <Calendar :size="12" />
              <span>{{ activePeriod }}</span>
            </div>
          </div>
        </div>

        <!-- Tag -->
        <div v-if="recurringTransaction.tag" class="flex items-center gap-2">
          <Badge variant="secondary" class="gap-1 px-2 py-0.5 text-xs">
            <TagIcon :size="10" />
            {{ recurringTransaction.tag.name }}
          </Badge>
        </div>
      </div>
    </div>
  </Card>

  <EditRecurringDialog
    v-if="showEditDialog && selectedRecurring"
    v-model:open="showEditDialog"
    :recurringTransaction="selectedRecurring"
    :categories="categories"
    :tags="tags"
  />

  <DeleteRecurringDialog
    v-if="showDeleteDialog && selectedRecurring"
    v-model:open="showDeleteDialog"
    :recurringTransaction="selectedRecurring"
  />
</template>
