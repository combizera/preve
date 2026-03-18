<script setup lang="ts">
import { Share2 } from 'lucide-vue-next';
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { ITransaction } from '@/types/models/transaction';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import transactionRoutes from '@/routes/transactions';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  transactionId: ITransaction['id'];
  title?: string;
}>();

const { t } = useI18n();

const handleShare = () => {
  const url = transactionRoutes.share({ transaction: props.transactionId }).url;

  router.post(
    url,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: (page) => {
        const generatedShareUrl = page.props.transactionShareUrl as string | undefined;

        if (generatedShareUrl) {
          navigator.clipboard.writeText(generatedShareUrl);
          toast.success(t('transactions.share.success'));
        }
      },
      onError: () => {
        toast.error(t('transactions.share.error'));
      },
    },
  );
};

</script>

<template>
  <DropdownMenuItem @click="handleShare" class="cursor-pointer">
    <Share2 class="size-4" />
    {{ title ?? t('generic.actions.share') }}
  </DropdownMenuItem>
</template>