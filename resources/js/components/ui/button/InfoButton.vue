<script setup lang="ts">
import { Info } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import transactionRoutes from '@/routes/transactions';
import { ITransaction } from '@/types/models/transaction';

const { t } = useI18n();

defineProps<{
  title?: string;
  transactionId: ITransaction['id'];
}>();
</script>

<template>
  <DropdownMenuItem as-child>
    <a target="_blank" :href="transactionRoutes.show(transactionId).url">
      <Info class="size-4" />
      {{ title ?? t('generic.actions.details') }}
    </a>
  </DropdownMenuItem>
</template>
