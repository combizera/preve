<script setup lang="ts">
import { Calendar, ChevronDown, Repeat, Tag as TagIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import ActionGroup from '@/components/ActionGroup.vue';
import DeleteRecurringDialog from '@/components/Recurring/DeleteRecurringDialog.vue';
import EditRecurringDialog from '@/components/Recurring/EditRecurringDialog.vue';
import { Badge } from '@/components/ui/badge';
import DeleteButton from '@/components/ui/button/DeleteButton.vue';
import EditButton from '@/components/ui/button/EditButton.vue';
import { Card } from '@/components/ui/card';
import { getIconComponent } from '@/lib/category-icons';
import { formatCentsToDisplay } from '@/lib/currency';
import { capitalizeFirstLetter, cn } from '@/lib/utils';
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

const frequencyText = computed(() => {
  const freq = capitalizeFirstLetter(props.recurringTransaction.frequency);
  const day = props.recurringTransaction.day_of_month;
  return `${freq} on day ${day}`;
});

const nextOccurrence = computed(() => {
  const today = new Date();
  const dayOfMonth = props.recurringTransaction.day_of_month;

  let nextDate = new Date(today.getFullYear(), today.getMonth(), dayOfMonth);

  if (nextDate < today) {
    if (props.recurringTransaction.frequency === 'monthly') {
      nextDate = new Date(today.getFullYear(), today.getMonth() + 1, dayOfMonth);
    } else {
      nextDate = new Date(today.getFullYear() + 1, today.getMonth(), dayOfMonth);
    }
  }

  return nextDate.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
});

const activePeriod = computed(() => {
  const startDate = new Date(props.recurringTransaction.start_date);
  const start = startDate.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });

  if (props.recurringTransaction.end_date) {
    const endDate = new Date(props.recurringTransaction.end_date);
    const end = endDate.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
    return `${start} - ${end}`;
  }

  return `Since ${start}`;
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
  <Card class="py-2 gap-2 rounded-md bg-sidebar overflow-hidden transition-colors">
    <!-- Main Content - Always Visible -->
    <div class="p-3 py-2 hover:bg-sidebar/80 cursor-pointer" @click="toggleExpand">
      <div class="flex items-center justify-between gap-3">
        <!-- Left Section: Main Info -->
        <div class="flex-1 min-w-0">
          <h3 class="text-sm font-semibold text-foreground leading-tight truncate">
            {{ recurringTransaction.description }}
          </h3>
          <div class="flex items-center gap-2 mt-1.5">
            <component :is="categoryIcon" :size="14" class="text-muted-foreground flex-shrink-0" />
            <span class="text-xs font-medium text-muted-foreground truncate">
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
        <div class="flex items-center gap-1 flex-shrink-0" @click.stop>
          <span :class="amountClass">R$ {{ formattedAmount }}</span>
          <ActionGroup>
            <EditButton @click="openEditDialog(recurringTransaction)" />
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
      class="px-4 py-2 pt-3 border-t border-border bg-sidebar/50"
    >
      <div class="space-y-3">
        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="space-y-1">
            <div class="text-muted-foreground/70 font-medium">Next Occurrence</div>
            <div class="flex items-center gap-1.5 text-foreground">
              <Calendar :size="12" />
              <span>{{ nextOccurrence }}</span>
            </div>
          </div>
          <div class="space-y-1">
            <div class="text-muted-foreground/70 font-medium">Active Period</div>
            <div class="flex items-center gap-1.5 text-foreground">
              <Calendar :size="12" />
              <span>{{ activePeriod }}</span>
            </div>
          </div>
        </div>

        <!-- Tag -->
        <div v-if="recurringTransaction.tag" class="flex items-center gap-2">
          <Badge variant="secondary" class="text-xs px-2 py-0.5 gap-1">
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
