<script setup lang="ts">
// File: resources/js/pages/admin/history/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, Filter, FileText, CheckCircle2, XCircle, Printer, QrCode, RotateCcw, Eye } from 'lucide-vue-next';

type HistoryItem = {
    id: number; action: string; action_label: string;
    keterangan?: string | null; created_at: string;
    user?: { name?: string | null } | null;
    surat?: {
        id: number; nomor_surat?: string | null;
        jenisSurat?: { nama?: string | null } | null;
        pemohon?: { name?: string | null } | null;
    } | null;
};

type Paginated = {
    data: HistoryItem[];
    from?: number | null; to?: number | null; total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    histories: Paginated;
    filters: { search?: string; action?: string; date_from?: string; date_to?: string };
}>();

const search   = ref(props.filters.search ?? '');
const action   = ref(props.filters.action ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo   = ref(props.filters.date_to ?? '');

function applyFilter() {
    router.get('/admin/history', {
        search: search.value || undefined,
        action: action.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
}

function formatDate(d: string) {
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(d));
}

const actionConfig: Record<string, { icon: unknown; color: string; bg: string }> = {
    created:    { icon: FileText,    color: 'text-indigo-600', bg: 'bg-indigo-100' },
    submitted:  { icon: FileText,    color: 'text-blue-600',   bg: 'bg-blue-100' },
    validated:  { icon: CheckCircle2, color: 'text-emerald-600', bg: 'bg-emerald-100' },
    approved:   { icon: CheckCircle2, color: 'text-emerald-600', bg: 'bg-emerald-100' },
    rejected:   { icon: XCircle,     color: 'text-red-600',    bg: 'bg-red-100' },
    generated:  { icon: FileText,    color: 'text-violet-600', bg: 'bg-violet-100' },
    printed:    { icon: Printer,     color: 'text-slate-600',  bg: 'bg-slate-100' },
    qr_scanned: { icon: QrCode,      color: 'text-cyan-600',   bg: 'bg-cyan-100' },
    qr_revoked: { icon: XCircle,     color: 'text-red-600',    bg: 'bg-red-100' },
    revised:    { icon: RotateCcw,   color: 'text-amber-600',  bg: 'bg-amber-100' },
};

function getConfig(action: string) {
    return actionConfig[action] ?? { icon: FileText, color: 'text-slate-500', bg: 'bg-slate-100' };
}
</script>

<template>
    <AdminLayout
        title="Riwayat Surat"
        subtitle="Audit trail semua aksi pada sistem surat"
        active-menu="history"
        :breadcrumbs="[{ label: 'Riwayat Surat' }]"
    >
        <Head title="Riwayat Surat" />

        <!-- Filter bar -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap gap-3">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Cari nama, surat..." class="h-9 w-52 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none focus:border-emerald-400" @keyup.enter="applyFilter" />
                </div>
                <select v-model="action" class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400">
                    <option value="">Semua Aksi</option>
                    <option value="created">Dibuat</option>
                    <option value="validated">Divalidasi</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="generated">Generate PDF</option>
                    <option value="printed">Dicetak</option>
                    <option value="qr_scanned">QR Discan</option>
                    <option value="qr_revoked">QR Dicabut</option>
                </select>
                <input v-model="dateFrom" type="date" class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" />
                <span class="flex items-center text-xs text-slate-400">s/d</span>
                <input v-model="dateTo" type="date" class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" />
                <button type="button" class="h-9 rounded-xl bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="applyFilter">Terapkan</button>
                <button type="button" class="h-9 rounded-xl border border-slate-200 px-4 text-xs text-slate-500 hover:bg-slate-50 transition-colors" @click="search='';action='';dateFrom='';dateTo='';applyFilter()">Reset</button>
                <p class="ml-auto flex items-center text-xs text-slate-400">{{ histories.total }} data</p>
            </div>
        </div>

        <!-- Timeline -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div v-if="histories.data.length === 0" class="flex flex-col items-center gap-2 py-16 text-center">
                <p class="text-sm text-slate-400">Belum ada riwayat aktivitas.</p>
            </div>

            <div class="divide-y divide-slate-100">
                <div
                    v-for="h in histories.data"
                    :key="h.id"
                    class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50/50 transition-colors"
                >
                    <!-- Icon -->
                    <div class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl" :class="getConfig(h.action).bg">
                        <component :is="getConfig(h.action).icon" class="size-4" :class="getConfig(h.action).color" />
                    </div>

                    <!-- Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ h.action_label }}</p>
                                <p v-if="h.surat?.jenisSurat?.nama" class="mt-0.5 text-xs text-slate-500">
                                    {{ h.surat.jenisSurat.nama }}
                                    <span v-if="h.surat.nomor_surat" class="font-mono text-slate-400"> · {{ h.surat.nomor_surat }}</span>
                                </p>
                                <p v-if="h.keterangan" class="mt-1 text-xs text-slate-400">{{ h.keterangan }}</p>
                            </div>
                            <Link v-if="h.surat?.id" :href="`/admin/surat/${h.surat.id}`" class="grid size-7 shrink-0 place-items-center rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-100 transition-colors">
                                <Eye class="size-3.5" />
                            </Link>
                        </div>
                        <div class="mt-1.5 flex items-center gap-2 text-[10px] text-slate-400">
                            <span>{{ h.user?.name ?? 'Sistem' }}</span>
                            <span>·</span>
                            <span>{{ formatDate(h.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="histories.links.length > 3" class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3">
                <Link
                    v-for="link in histories.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="[link.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200', !link.url ? 'pointer-events-none opacity-40' : '']"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>
