<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile, preferences } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';

const { t } = useI18n();

const sidebarNavItems: NavItem[] = [
  {
    title: t('settings.nav.profile'),
    href: editProfile(),
  },
  {
    title: t('settings.nav.preferences'),
    href: preferences(),
  },
  {
    title: t('settings.nav.password'),
    href: editPassword(),
  },
  {
    title: t('settings.nav.twoFactorAuth'),
    href: show(),
  },
];

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
  <div class="px-4 py-6">
    <Heading
      :title="t('settings.title')"
      :description="t('settings.description')"
    />

    <div class="flex flex-col lg:flex-row lg:space-x-12">
      <aside class="w-full max-w-xl lg:w-48">
        <nav
          class="flex flex-col space-y-1 space-x-0"
          :aria-label="t('settings.title')"
        >
          <Button
            v-for="item in sidebarNavItems"
            :key="toUrl(item.href)"
            variant="ghost"
            :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentUrl(item.href) },
                        ]"
            as-child
          >
            <Link :href="item.href">
              <component :is="item.icon" class="h-4 w-4" />
              {{ item.title }}
            </Link>
          </Button>
        </nav>
      </aside>

      <Separator class="my-6 lg:hidden" />

      <div class="flex-1 md:max-w-2xl">
        <section class="max-w-xl space-y-12">
          <slot />
        </section>
      </div>
    </div>
  </div>
</template>
