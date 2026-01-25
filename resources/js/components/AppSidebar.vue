<script setup lang="ts">
import { ArrowRightLeft, Github, Home, RefreshCw, Tags } from 'lucide-vue-next';

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
import tags from '@/routes/tags';
import transactions from '@/routes/transactions';
import { type NavItem } from '@/types';

const mainNavItems: NavItem[] = [
  {
    title: 'Home',
    href: dashboard(),
    icon: Home,
  },
  {
    title: 'Transactions',
    href: transactions.index(),
    icon: ArrowRightLeft,
  },
  {
    title: 'Recurring',
    href: '#',
    icon: RefreshCw,
  },
  {
    title: 'Categories',
    href: categories.index(),
    icon: Tags,
  },
  {
    title: 'Tags',
    href: tags.index(),
    icon: Tags,
  }
];

const footerNavItems: NavItem[] = [
  {
    title: 'Github',
    href: 'https://github.com/combizera/preve',
    icon: Github,
  },
];
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
