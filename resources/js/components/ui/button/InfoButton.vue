<script setup lang="ts">
import { Info } from 'lucide-vue-next';

import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { ITransaction } from '@/types/models/transaction';
import transactionRoutes from '@/routes/transactions';

defineProps<{
  title?: string;
  transactionId: ITransaction['id'];
}>();

</script>

<template>
  <DropdownMenuItem as-child>
    <a target="_blank" :href="transactionRoutes.show(transactionId).url">
      <Info class="size-4" />
      {{ title ?? 'Details' }}
    </a>
  </DropdownMenuItem>
</template>
