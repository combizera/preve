<script setup lang="ts">
import { ArrowRightLeft, Github, Home, RefreshCw, Tags } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import categories from '@/routes/categories';
import recurring from '@/routes/recurring';
import tags from '@/routes/tags';
import transactions from '@/routes/transactions';
import { type NavItem } from '@/types';

const { t } = useI18n();

const mainNavItems = computed<NavItem[]>(() => [
  {
    title: t('generic.sidebar.home'),
    href: dashboard(),
    icon: Home,
  },
  {
    title: t('generic.sidebar.transactions'),
    href: transactions.index(),
    icon: ArrowRightLeft,
  },
  {
    title: t('generic.sidebar.recurring'),
    href: recurring.index(),
    icon: RefreshCw,
  },
  {
    title: t('generic.sidebar.category'),
    href: categories.index(),
    icon: Tags,
  },
  {
    title: t('generic.sidebar.tags'),
    href: tags.index(),
    icon: Tags,
  }
]);

const footerNavItems = computed<NavItem[]>(() => [
  {
    title: 'Github',
    href: 'https://github.com/combizera/preve',
    icon: Github,
  },
]);
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <AppLogo />
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
