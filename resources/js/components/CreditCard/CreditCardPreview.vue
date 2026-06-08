<script setup lang="ts">
import { Nfc, WalletCards } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

import type { CreditCardColor } from '@/lib/credit-card-colors';
import { getCreditCardColorClass } from '@/lib/credit-card-colors';
import { cn } from '@/lib/utils';

interface Props {
  name?: string | null;
  lastFour?: string | null;
  color?: CreditCardColor;
}

defineProps<Props>();

const { t } = useI18n();
</script>

<template>
  <div
    :class="
      cn(
        'relative flex aspect-[1.586] w-full flex-col justify-between overflow-hidden rounded-xl p-5 text-white shadow-lg transition-colors',
        color ? getCreditCardColorClass(color, 'picker') : 'bg-neutral-700',
      )
    "
  >
    <div
      class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/20 via-transparent to-black/40"
    />

    <div class="relative flex items-start justify-between">
      <span class="text-xs font-semibold tracking-widest uppercase opacity-90">
        {{ t('creditCards.preview.type') }}
      </span>
      <WalletCards class="size-5 opacity-90" />
    </div>

    <div class="relative space-y-3">
      <Nfc class="size-5 rotate-90 opacity-90" />
      <p class="font-mono text-base tracking-[0.2em]">
        ···· ···· ···· {{ lastFour || '····' }}
      </p>
      <p class="truncate text-sm font-medium">
        {{ name || t('creditCards.preview.placeholder') }}
      </p>
    </div>
  </div>
</template>
