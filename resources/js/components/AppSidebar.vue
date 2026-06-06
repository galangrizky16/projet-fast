<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    FileCheck2,
    FilePlus2,
    FileStack,
    FolderGit2,
    LayoutGrid,
    ScrollText,
} from 'lucide-vue-next';
import { computed } from 'vue';
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
import type { NavItem } from '@/types';
import { useRolePrefix } from '@/composables/useRolePrefix';

const { rolePrefix: routePrefix, roleSlug } = useRolePrefix();

const mainNavItems = computed<NavItem[]>(() => {
    if (roleSlug.value.includes('admin')) {
        return [
            {
                title: 'Dashboard',
                href: `${routePrefix.value}/dashboard`,
                icon: LayoutGrid,
            },
            {
                title: 'Pembuatan Surat',
                href: `${routePrefix.value}/surat/create`,
                icon: FilePlus2,
            },
            {
                title: 'Semua Pengajuan',
                href: `${routePrefix.value}/surat`,
                icon: FileStack,
            },
            {
                title: 'Template Surat',
                href: `${routePrefix.value}/templates`,
                icon: ScrollText,
            },
        ];
    }

    if (
        roleSlug.value.includes('kaprodi') ||
        roleSlug.value.includes('dekan')
    ) {
        return [
            {
                title: 'Dashboard Approval',
                href: '/approval/dashboard',
                icon: FileCheck2,
            },
        ];
    }

    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
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
