<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FileCheck2, FilePlus2, FileStack, FolderGit2, LayoutGrid, ScrollText } from 'lucide-vue-next';
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

type PageProps = {
    auth?: {
        user?: {
            role?: {
                slug?: string | null;
                nama?: string | null;
            } | null;
        } | null;
    };
};

const page = usePage<PageProps>();

const roleSlug = computed(() => {
    const slug = String(page.props.auth?.user?.role?.slug ?? '').trim().toLowerCase();

    if (slug !== '') {
        return slug;
    }

    return String(page.props.auth?.user?.role?.nama ?? '').trim().toLowerCase();
});

const mainNavItems = computed<NavItem[]>(() => {
    if (roleSlug.value.includes('admin')) {
        return [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Pembuatan Surat',
                href: '/admin/surat/create',
                icon: FilePlus2,
            },
            {
                title: 'Semua Pengajuan',
                href: '/admin/dashboard',
                icon: FileStack,
            },
            {
                title: 'Template Surat',
                href: '/admin/templates',
                icon: ScrollText,
            },
        ];
    }

    if (roleSlug.value.includes('kaprodi') || roleSlug.value.includes('dekan')) {
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
