<script setup lang="ts">
// File: resources/js/layouts/AdminLayout.vue
// Menggantikan: layouts/fast/AdminWorkspaceLayout.vue
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    LayoutDashboard, FilePlus, ClipboardList, CheckSquare,
    Archive, Code2, List, Users, Settings, LogOut,
    Bell, Search, ChevronRight,
} from 'lucide-vue-next';

type PageProps = {
    auth?: { user?: { name?: string; role?: { nama?: string; slug?: string } } };
    flash?: { success?: string; error?: string };
    notifications?: { count: number };
};

const props = withDefaults(defineProps<{
    title: string;
    subtitle?: string;
    activeMenu?: string;
    breadcrumbs?: Array<{ label: string; href?: string }>;
}>(), {
    subtitle: '',
    activeMenu: 'dashboard',
    breadcrumbs: () => [],
});

const page        = usePage<PageProps>();
const sidebarOpen = ref(true);
const searchQuery = ref('');

const user         = computed(() => page.props.auth?.user);
const userName     = computed(() => user.value?.name ?? 'Admin');
const userRole     = computed(() => user.value?.role?.nama ?? 'Administrator');
const userInitials = computed(() => userName.value.split(' ').slice(0, 2).map((n: string) => n[0]).join('').toUpperCase());
const notifCount   = computed(() => page.props.notifications?.count ?? 0);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError   = computed(() => page.props.flash?.error);

type NavItem = {
    key: string;
    label: string;
    href: string;
    icon: unknown;
    badge?: number | string;
    badgeVariant?: 'default' | 'warning' | 'danger';
};

type NavSection = {
    title: string;
    items: NavItem[];
};

const navSections = computed((): NavSection[] => [
    {
        title: 'Utama',
        items: [
            { key: 'dashboard', label: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
        ],
    },
    {
        title: 'Manajemen Surat',
        items: [
            { key: 'letters.create', label: 'Buat Surat', href: '/admin/surat/create', icon: FilePlus },
            { key: 'letters.index', label: 'Semua Pengajuan', href: '/admin/dashboard', icon: ClipboardList, badge: 12 },
            { key: 'approval', label: 'Antrian Approval', href: '/approval/dashboard', icon: CheckSquare, badge: 5, badgeVariant: 'warning' },
            { key: 'archive', label: 'Arsip & Unduh', href: '/admin/dashboard', icon: Archive },
        ],
    },
    {
        title: 'Konfigurasi',
        items: [
            { key: 'templates', label: 'Template Surat', href: '/admin/templates', icon: Code2 },
            { key: 'categories', label: 'Kategori Surat', href: '/admin/dashboard', icon: List },
            { key: 'roles', label: 'Manajemen Role', href: '/admin/dashboard', icon: Users },
        ],
    },
]);

function isActive(key: string): boolean {
    return props.activeMenu === key || props.activeMenu?.startsWith(key + '.');
}

function logout(): void {
    router.post('/logout');
}
</script>

<template>
    <Head :title="props.title" />

    <div class="flex h-screen overflow-hidden bg-slate-50">

        <!-- ── Sidebar ── -->
        <aside
            class="flex flex-col border-r border-slate-200 bg-white transition-all duration-200"
            :class="sidebarOpen ? 'w-56' : 'w-16'"
        >
            <!-- Logo -->
            <div class="flex h-14 items-center gap-3 border-b border-slate-100 px-4">
                <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-indigo-600 text-white shadow-sm shadow-indigo-200">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <transition name="fade">
                    <div v-if="sidebarOpen" class="min-w-0">
                        <p class="truncate text-sm font-bold tracking-tight text-slate-900">FAST Academic</p>
                        <p class="text-[10px] font-medium uppercase tracking-widest text-slate-400">FMIKOM Portal</p>
                    </div>
                </transition>
            </div>

            <!-- User info -->
            <div v-if="sidebarOpen" class="mx-3 mt-3 rounded-xl bg-slate-50 px-3 py-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-700">
                        {{ userInitials }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-slate-900">{{ userName }}</p>
                        <p class="truncate text-[10px] text-slate-400">{{ userRole }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="mx-auto mt-3">
                <div class="grid size-8 place-items-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-700">
                    {{ userInitials }}
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-2 py-2">
                <template v-for="section in navSections" :key="section.title">
                    <p v-if="sidebarOpen" class="mb-1 mt-3 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 first:mt-2">
                        {{ section.title }}
                    </p>
                    <div v-else class="my-2 border-t border-slate-100" />

                    <Link
                        v-for="item in section.items"
                        :key="item.key"
                        :href="item.href"
                        class="mb-0.5 flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors"
                        :class="isActive(item.key)
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'"
                        :title="!sidebarOpen ? item.label : undefined"
                    >
                        <component
                            :is="item.icon"
                            class="size-4 shrink-0"
                            :class="isActive(item.key) ? 'text-indigo-600' : 'text-slate-400'"
                        />
                        <transition name="fade">
                            <span v-if="sidebarOpen" class="flex-1 truncate">{{ item.label }}</span>
                        </transition>
                        <transition name="fade">
                            <span
                                v-if="sidebarOpen && item.badge"
                                class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none"
                                :class="{
                                    'bg-indigo-100 text-indigo-700': !item.badgeVariant || item.badgeVariant === 'default',
                                    'bg-amber-100 text-amber-700': item.badgeVariant === 'warning',
                                    'bg-red-100 text-red-700': item.badgeVariant === 'danger',
                                }"
                            >
                                {{ item.badge }}
                            </span>
                        </transition>
                    </Link>
                </template>
            </nav>

            <!-- Bottom actions -->
            <div class="border-t border-slate-100 px-2 py-2">
                <Link
                    href="/settings/profile"
                    class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700"
                    :title="!sidebarOpen ? 'Pengaturan' : undefined"
                >
                    <Settings class="size-4 shrink-0 text-slate-400" />
                    <span v-if="sidebarOpen">Pengaturan</span>
                </Link>
                <button
                    type="button"
                    class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-red-500 transition-colors hover:bg-red-50"
                    :title="!sidebarOpen ? 'Keluar' : undefined"
                    @click="logout"
                >
                    <LogOut class="size-4 shrink-0" />
                    <span v-if="sidebarOpen">Keluar</span>
                </button>
            </div>
        </aside>

        <!-- ── Main ── -->
        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="flex h-14 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5">
                <!-- Left: toggle + breadcrumb -->
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                        @click="sidebarOpen = !sidebarOpen"
                    >
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <nav class="flex items-center gap-1.5 text-sm">
                        <span class="text-slate-400">FAST Academic</span>
                        <ChevronRight class="size-3.5 text-slate-300" />
                        <template v-if="props.breadcrumbs.length">
                            <template v-for="(bc, i) in props.breadcrumbs" :key="i">
                                <Link v-if="bc.href" :href="bc.href" class="text-slate-400 hover:text-slate-600">{{ bc.label }}</Link>
                                <span v-else class="font-semibold text-slate-800">{{ bc.label }}</span>
                                <ChevronRight v-if="i < props.breadcrumbs.length - 1" class="size-3.5 text-slate-300" />
                            </template>
                        </template>
                        <span v-else class="font-semibold text-slate-800">{{ props.title }}</span>
                    </nav>
                </div>

                <!-- Right: search + notif -->
                <div class="flex items-center gap-2">
                    <div class="relative hidden md:block">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari surat, mahasiswa..."
                            class="h-8 w-52 rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100"
                        />
                    </div>
                    <button type="button" class="relative rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 hover:bg-slate-50">
                        <Bell class="size-4" />
                        <span v-if="notifCount > 0" class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white">
                            {{ notifCount > 9 ? '9+' : notifCount }}
                        </span>
                    </button>
                </div>
            </header>

            <!-- Flash messages -->
            <transition name="slide-down">
                <div v-if="flashSuccess" class="mx-5 mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <svg class="size-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm font-medium text-emerald-800">{{ flashSuccess }}</p>
                </div>
            </transition>

            <transition name="slide-down">
                <div v-if="flashError" class="mx-5 mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <svg class="size-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800">{{ flashError }}</p>
                </div>
            </transition>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto">
                <!-- Optional page header slot -->
                <slot name="header">
                    <div v-if="props.subtitle || props.title" class="border-b border-slate-100 bg-white px-6 py-4">
                        <p v-if="props.subtitle" class="text-xs font-medium text-slate-400">{{ props.subtitle }}</p>
                        <h1 class="mt-0.5 text-xl font-semibold tracking-tight text-slate-900">{{ props.title }}</h1>
                    </div>
                </slot>

                <div class="p-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s, width .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-down-enter-active, .slide-down-leave-active { transition: all .2s; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
