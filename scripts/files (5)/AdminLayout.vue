<script setup lang="ts">
// File: resources/js/layouts/AdminLayout.vue
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import {
    LayoutDashboard, FilePlus2, ClipboardList, CheckSquare,
    Archive, FileCode2, Tag, Settings, LogOut, Bell,
    Search, ChevronRight, Menu, X, History, QrCode, FileText,
} from 'lucide-vue-next';

type BreadcrumbItem = { label: string; href?: string };
type PageProps = {
    auth?: { user?: { name?: string; email?: string; role?: { nama?: string; slug?: string } } };
    flash?: { success?: string; error?: string; warning?: string };
    notif_count?: number;
};

const props = withDefaults(defineProps<{
    title: string;
    subtitle?: string;
    activeMenu?: string;
    breadcrumbs?: BreadcrumbItem[];
}>(), {
    subtitle: '',
    activeMenu: 'dashboard',
    breadcrumbs: () => [],
});

const page        = usePage<PageProps>();
const sidebarOpen = ref(true);
const mobileOpen  = ref(false);
const isMobile    = ref(false);

const user         = computed(() => page.props.auth?.user);
const userName     = computed(() => user.value?.name ?? 'Admin');
const userRole     = computed(() => user.value?.role?.nama ?? 'Administrator');
const userSlug     = computed(() => user.value?.role?.slug ?? 'admin');
const userInitials = computed(() =>
    userName.value.split(' ').slice(0, 2).map((n: string) => n[0]).join('').toUpperCase()
);
const notifCount   = computed(() => page.props.notif_count ?? 0);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError   = computed(() => page.props.flash?.error);
const flashWarning = computed(() => page.props.flash?.warning);

// Cek apakah user adalah approver (dekan/kaprodi)
const isApprover = computed(() => ['dekan', 'kaprodi'].includes(userSlug.value));
const isAdmin    = computed(() => ['admin', 'super-admin', 'sekretaris-fakultas'].includes(userSlug.value));

function checkMobile() {
    isMobile.value = window.innerWidth < 1024;
    if (isMobile.value) sidebarOpen.value = false;
    else mobileOpen.value = false;
}
onMounted(() => { checkMobile(); window.addEventListener('resize', checkMobile); });
onUnmounted(() => window.removeEventListener('resize', checkMobile));
watch(() => page.url, () => { mobileOpen.value = false; });

type NavItem = {
    key: string; label: string; href: string; icon: unknown;
    badge?: number | string; badgeColor?: string;
    onlyFor?: string[]; // role slugs yang boleh lihat
};

const navGroups = computed(() => {
    const groups = [];

    // ── Utama ──────────────────────────────────────────────────
    groups.push({
        title: 'Utama',
        items: [
            { key: 'dashboard', label: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
        ] as NavItem[],
    });

    // ── Surat (hanya untuk admin/sekretaris) ──────────────────
    if (isAdmin.value) {
        groups.push({
            title: 'Surat',
            items: [
                { key: 'letters.create', label: 'Buat Surat', href: '/admin/surat/create', icon: FilePlus2 },
                {
                    key: 'letters.index', label: 'Semua Pengajuan', href: '/admin/surat',
                    icon: ClipboardList,
                    badge: notifCount.value || undefined, badgeColor: 'amber',
                },
                { key: 'history', label: 'Riwayat Surat', href: '/admin/history', icon: History },
                { key: 'archive', label: 'Arsip & Unduh', href: '/admin/archive', icon: Archive },
            ] as NavItem[],
        });
    }

    // ── Approval (untuk semua role yang punya akses approval) ─
    groups.push({
        title: 'Approval',
        items: [
            {
                key: 'approval', label: 'Antrian Approval', href: '/approval/dashboard',
                icon: CheckSquare,
                badge: notifCount.value || undefined, badgeColor: 'amber',
            },
        ] as NavItem[],
    });

    // ── Pengaturan (hanya admin) ───────────────────────────────
    if (isAdmin.value) {
        groups.push({
            title: 'Pengaturan',
            items: [
                { key: 'templates', label: 'Template Surat', href: '/admin/templates', icon: FileCode2 },
                { key: 'categories', label: 'Kategori Surat', href: '/admin/categories', icon: Tag },
                { key: 'qr', label: 'Kelola QR Code', href: '/admin/qr', icon: QrCode },
            ] as NavItem[],
        });
    }

    return groups;
});

function isActive(key: string) {
    return props.activeMenu === key || props.activeMenu?.startsWith(key + '.');
}

function toggleSidebar() {
    if (isMobile.value) mobileOpen.value = !mobileOpen.value;
    else sidebarOpen.value = !sidebarOpen.value;
}

function logout() { router.post('/logout'); }

const showSidebar    = computed(() => isMobile.value ? mobileOpen.value : true);
const sidebarExpanded = computed(() => isMobile.value ? true : sidebarOpen.value);
</script>

<template>
    <Head :title="title" />

    <div class="flex h-screen overflow-hidden bg-slate-50 font-sans">

        <!-- Mobile overlay -->
        <Transition name="fade">
            <div v-if="isMobile && mobileOpen"
                class="fixed inset-0 z-20 bg-black/40 lg:hidden"
                @click="mobileOpen = false" />
        </Transition>

        <!-- ── Sidebar ── -->
        <Transition name="slide-sidebar">
            <aside v-show="showSidebar"
                class="fixed inset-y-0 left-0 z-30 flex flex-col border-r border-slate-200 bg-white transition-all duration-200 lg:relative lg:z-auto"
                :class="sidebarExpanded ? 'w-56' : 'w-[60px]'">

                <!-- Logo -->
                <div class="flex h-14 shrink-0 items-center gap-2.5 border-b border-slate-100 px-3">
                    <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-600 text-white shadow-sm">
                        <FileText class="size-4" />
                    </div>
                    <Transition name="fade-text">
                        <div v-if="sidebarExpanded" class="min-w-0 overflow-hidden">
                            <p class="truncate text-[13px] font-bold tracking-tight text-slate-900">FAST Academic</p>
                            <p class="text-[10px] uppercase tracking-widest text-slate-400">FMIKOM</p>
                        </div>
                    </Transition>
                </div>

                <!-- User chip -->
                <div class="shrink-0 px-2 pt-3">
                    <div class="flex items-center gap-2 rounded-xl px-2 py-2"
                        :class="sidebarExpanded ? 'bg-slate-50' : 'justify-center'">
                        <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-emerald-100 text-[11px] font-bold text-emerald-700">
                            {{ userInitials }}
                        </div>
                        <Transition name="fade-text">
                            <div v-if="sidebarExpanded" class="min-w-0">
                                <p class="truncate text-xs font-semibold text-slate-900">{{ userName }}</p>
                                <p class="truncate text-[10px] text-slate-400">{{ userRole }}</p>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-2">
                    <template v-for="group in navGroups" :key="group.title">
                        <Transition name="fade-text">
                            <p v-if="sidebarExpanded"
                                class="mb-1 mt-3 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 first:mt-1">
                                {{ group.title }}
                            </p>
                        </Transition>
                        <div v-if="!sidebarExpanded" class="my-1.5 border-t border-slate-100" />

                        <Link
                            v-for="item in group.items" :key="item.key"
                            :href="item.href"
                            class="mb-0.5 flex items-center gap-2.5 rounded-lg px-2 py-2 text-[13px] font-medium transition-colors"
                            :class="[
                                isActive(item.key) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700',
                                !sidebarExpanded ? 'justify-center' : '',
                            ]"
                            :title="!sidebarExpanded ? item.label : undefined"
                        >
                            <component :is="item.icon" class="size-4 shrink-0"
                                :class="isActive(item.key) ? 'text-emerald-600' : 'text-slate-400'" />
                            <Transition name="fade-text">
                                <span v-if="sidebarExpanded" class="flex-1 truncate">{{ item.label }}</span>
                            </Transition>
                            <Transition name="fade-text">
                                <span v-if="sidebarExpanded && item.badge"
                                    class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none"
                                    :class="item.badgeColor === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
                                    {{ item.badge }}
                                </span>
                            </Transition>
                        </Link>
                    </template>
                </nav>

                <!-- Bottom -->
                <div class="shrink-0 border-t border-slate-100 px-2 py-2">
                    <Link href="/settings/profile"
                        class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-[13px] font-medium text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700"
                        :class="!sidebarExpanded ? 'justify-center' : ''"
                        :title="!sidebarExpanded ? 'Pengaturan' : undefined">
                        <Settings class="size-4 shrink-0 text-slate-400" />
                        <Transition name="fade-text">
                            <span v-if="sidebarExpanded">Pengaturan</span>
                        </Transition>
                    </Link>
                    <button type="button"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-[13px] font-medium text-red-500 transition-colors hover:bg-red-50"
                        :class="!sidebarExpanded ? 'justify-center' : ''"
                        :title="!sidebarExpanded ? 'Keluar' : undefined"
                        @click="logout">
                        <LogOut class="size-4 shrink-0" />
                        <Transition name="fade-text">
                            <span v-if="sidebarExpanded">Keluar</span>
                        </Transition>
                    </button>
                </div>
            </aside>
        </Transition>

        <!-- ── Content ── -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="flex h-14 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-5">
                <div class="flex items-center gap-3">
                    <button type="button"
                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                        @click="toggleSidebar">
                        <Menu class="size-5" />
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="hidden items-center gap-1 text-sm md:flex">
                        <span class="text-slate-400">FAST</span>
                        <ChevronRight class="size-3.5 text-slate-300" />
                        <template v-if="props.breadcrumbs?.length">
                            <template v-for="(bc, i) in props.breadcrumbs" :key="i">
                                <Link v-if="bc.href" :href="bc.href"
                                    class="text-slate-400 hover:text-slate-600 transition-colors">
                                    {{ bc.label }}
                                </Link>
                                <span v-else class="font-medium text-slate-800">{{ bc.label }}</span>
                                <ChevronRight v-if="i < props.breadcrumbs.length - 1" class="size-3.5 text-slate-300" />
                            </template>
                        </template>
                        <span v-else class="font-medium text-slate-800">{{ props.title }}</span>
                    </nav>
                </div>

                <!-- Right -->
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="relative rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 hover:bg-slate-50 transition-colors">
                        <Bell class="size-4" />
                        <span v-if="notifCount > 0"
                            class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white">
                            {{ notifCount > 9 ? '9+' : notifCount }}
                        </span>
                    </button>
                </div>
            </header>

            <!-- Flash messages -->
            <div class="shrink-0">
                <Transition name="flash">
                    <div v-if="flashSuccess" class="mx-4 mt-3 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5">
                        <div class="size-5 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                            <svg class="size-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-emerald-800">{{ flashSuccess }}</p>
                    </div>
                </Transition>
                <Transition name="flash">
                    <div v-if="flashError" class="mx-4 mt-3 flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5">
                        <div class="size-5 rounded-full bg-red-500 flex items-center justify-center shrink-0">
                            <X class="size-3 text-white" />
                        </div>
                        <p class="text-sm font-medium text-red-800">{{ flashError }}</p>
                    </div>
                </Transition>
                <Transition name="flash">
                    <div v-if="flashWarning" class="mx-4 mt-3 flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5">
                        <p class="text-sm font-medium text-amber-800">⚠ {{ flashWarning }}</p>
                    </div>
                </Transition>
            </div>

            <!-- Page header -->
            <div v-if="subtitle || $slots.actions"
                class="shrink-0 border-b border-slate-100 bg-white px-5 py-3.5 flex items-center justify-between gap-4">
                <div>
                    <p v-if="subtitle" class="text-xs text-slate-400">{{ subtitle }}</p>
                    <h1 class="text-lg font-semibold text-slate-900">{{ title }}</h1>
                </div>
                <div v-if="$slots.actions" class="flex items-center gap-2 shrink-0">
                    <slot name="actions" />
                </div>
            </div>

            <!-- Main content -->
            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-screen-xl p-4 lg:p-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.fade-text-enter-active { transition: opacity .1s, width .15s; }
.fade-text-leave-active { transition: opacity .08s; }
.fade-text-enter-from, .fade-text-leave-to { opacity: 0; width: 0; overflow: hidden; }
.flash-enter-active, .flash-leave-active { transition: all .2s; }
.flash-enter-from, .flash-leave-to { opacity: 0; transform: translateY(-6px); }
.slide-sidebar-enter-active, .slide-sidebar-leave-active { transition: transform .2s; }
.slide-sidebar-enter-from, .slide-sidebar-leave-to { transform: translateX(-100%); }
</style>
