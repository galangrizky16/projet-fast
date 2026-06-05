<script setup lang="ts">
// resources/js/pages/fast/user/History.vue
import FastLayout from '@/layouts/FastLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { defineAsyncComponent, ref } from 'vue';

const PdfViewer = defineAsyncComponent(() => import('@/components/PdfViewer.vue'));
import {
    FileText, CheckCircle2, XCircle, Download, Eye,
    Search, ChevronLeft, ChevronRight, AlertCircle,
    RotateCcw, X,
} from 'lucide-vue-next';

type Surat = {
    id: number; reference: string; jenisSurat: string;
    status: string; keperluan: string;
    rejectionReason?: string | null;
    submittedAt?: string | null; neededAt?: string | null;
    nomor_surat?: string | null;
    canCancel?: boolean;
    jenisSuratId?: number | null;
};

const props = defineProps<{
    surats: {
        data: Surat[];
        total: number;
        current_page: number;
        last_page: number;
        per_page: number;
    };
    filters: { search?: string; status?: string };
}>();

const search           = ref(props.filters.search ?? '');
const status           = ref(props.filters.status ?? '');
const expandedReasonId = ref<number | null>(null);
const cancelConfirmId  = ref<number | null>(null);

// ── [BARU] PDF Viewer state ─────────────────────────────────────────────────
const viewerOpen   = ref(false);
const viewerUrl    = ref<string | null>(null);
const viewerTitle  = ref('');
const viewerType   = ref<'html' | 'pdf'>('html');
const viewerStatus = ref('');
const viewerNomor  = ref<string | null>(null);

function openViewer(item: Surat, mode: 'preview' | 'download') {
    if (mode === 'preview') {
        viewerUrl.value   = `/admin/surat/${item.id}/template-preview`;
        viewerTitle.value = `Preview — ${item.jenisSurat}`;
        viewerType.value  = 'html';
    } else {
        viewerUrl.value   = `/admin/surat/${item.id}/pdf`;
        viewerTitle.value = `${item.jenisSurat} — ${item.reference}`;
        viewerType.value  = 'pdf';
    }
    viewerStatus.value = item.status;
    viewerNomor.value  = item.nomor_surat ?? item.reference;
    viewerOpen.value   = true;
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => { viewerUrl.value = null; }, 200);
}
// ── [END BARU] ──────────────────────────────────────────────────────────────

const STATUS_STEPS = [
    { key: 'pending',           short: 'Diajukan' },
    { key: 'validated_admin',   short: 'Admin' },
    { key: 'approved_kaprodi',  short: 'Kaprodi' },
    { key: 'approved_dekan',    short: 'Dekan' },
    { key: 'finished',          short: 'Selesai' },
];

function getStepIndex(status: string): number {
    if (status === 'rejected') return -1;
    return STATUS_STEPS.findIndex(s => s.key === status);
}

function statusLabel(status: string) {
    const map: Record<string, string> = {
        pending: 'Menunggu Validasi', validated_admin: 'Divalidasi Admin',
        approved_kaprodi: 'Disetujui Kaprodi', approved_dekan: 'Disetujui Dekan',
        finished: 'Selesai', rejected: 'Ditolak', cancelled: 'Dibatalkan',
    };
    return map[status] ?? 'Diproses';
}

function statusBadgeClass(status: string) {
    const map: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-700',
        validated_admin: 'bg-sky-100 text-sky-700',
        approved_kaprodi: 'bg-emerald-100 text-emerald-700',
        approved_dekan: 'bg-green-100 text-green-700',
        finished: 'bg-lime-100 text-lime-800',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-slate-100 text-slate-600',
    };
    return map[status] ?? 'bg-slate-100 text-slate-600';
}

function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(date));
}

function applyFilter() {
    router.get('/fast/user/history', { search: search.value, status: status.value }, { preserveState: true });
}

function toggleReason(id: number) {
    expandedReasonId.value = expandedReasonId.value === id ? null : id;
}

function confirmCancel(id: number) {
    cancelConfirmId.value = id;
}

function cancelSurat(id: number) {
    router.post(`/fast/user/surat/${id}/cancel`, {}, {
        preserveScroll: true,
        onSuccess: () => { cancelConfirmId.value = null; },
    });
}

function resubmit(item: Surat) {
    const q = item.jenisSuratId ? `?jenis=${item.jenisSuratId}` : '';
    router.visit(`/fast/user/ajukan${q}`);
}

function goToPage(page: number) {
    router.get('/fast/user/history', { search: search.value, status: status.value, page }, { preserveState: true });
}
</script>

<template>
    <FastLayout title="Riwayat Surat" subtitle="Semua pengajuan surat Anda" active-menu="history"
        :breadcrumbs="[{ label: 'Dashboard', href: '/fast/user/dashboard' }, { label: 'Riwayat Surat' }]">
        <Head title="Riwayat Surat — FAST" />

        <!-- Filter -->
        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <div class="relative flex-1 min-w-[180px]">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                <input v-model="search" type="text" placeholder="Cari jenis surat atau keperluan..."
                    class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400"
                    @keyup.enter="applyFilter" />
            </div>
            <select v-model="status"
                class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-emerald-400"
                @change="applyFilter">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu Validasi</option>
                <option value="validated_admin">Divalidasi Admin</option>
                <option value="approved_kaprodi">Disetujui Kaprodi</option>
                <option value="approved_dekan">Disetujui Dekan</option>
                <option value="finished">Selesai</option>
                <option value="rejected">Ditolak</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            <button type="button"
                class="h-9 rounded-xl bg-emerald-600 px-4 text-sm font-medium text-white hover:bg-emerald-700 transition-colors"
                @click="applyFilter">Cari</button>
            <span class="ml-auto text-xs text-slate-400">{{ surats.total }} pengajuan</span>
        </div>

        <!-- Empty state -->
        <div v-if="surats.data.length === 0"
            class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center">
            <FileText class="mx-auto mb-3 size-12 text-slate-300" />
            <p class="text-sm font-medium text-slate-500">Tidak ada pengajuan ditemukan</p>
        </div>

        <!-- Tabel Riwayat -->
        <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="w-12 px-4 py-3">No</th>
                            <th class="px-4 py-3">Jenis Surat</th>
                            <th class="w-40 px-4 py-3">Tanggal</th>
                            <th class="w-44 px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template v-for="(item, i) in surats.data" :key="item.id">
                            <tr class="align-top transition-colors hover:bg-slate-50/60">
                                <td class="px-4 py-3 text-slate-400">
                                    {{ (surats.current_page - 1) * surats.per_page + i + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ item.jenisSurat }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">
                                        <span class="font-mono">{{ item.reference }}</span>
                                        <span v-if="item.keperluan"> &middot; {{ item.keperluan }}</span>
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <p>{{ formatDate(item.submittedAt) }}</p>
                                    <p v-if="item.neededAt" class="text-xs text-slate-400">Butuh: {{ formatDate(item.neededAt) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                                        :class="statusBadgeClass(item.status)">
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <button type="button" title="Preview"
                                            class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                                            @click="openViewer(item, 'preview')">
                                            <Eye class="size-4" />
                                        </button>
                                        <button v-if="item.status === 'finished'" type="button" title="Download PDF"
                                            class="grid size-8 place-items-center rounded-lg bg-emerald-600 text-white transition hover:bg-emerald-700"
                                            @click="openViewer(item, 'download')">
                                            <Download class="size-4" />
                                        </button>
                                        <button v-if="item.status === 'rejected' && item.rejectionReason" type="button" title="Lihat alasan"
                                            class="grid size-8 place-items-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                                            @click="toggleReason(item.id)">
                                            <AlertCircle class="size-4" />
                                        </button>
                                        <button v-if="item.status === 'rejected'" type="button" title="Ajukan Ulang"
                                            class="flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100"
                                            @click="resubmit(item)">
                                            <RotateCcw class="size-3.5" /> Ulang
                                        </button>
                                        <button v-if="item.status === 'pending'" type="button" title="Batalkan"
                                            class="flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100"
                                            @click="confirmCancel(item.id)">
                                            <X class="size-3.5" /> Batal
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris alasan penolakan -->
                            <tr v-if="expandedReasonId === item.id && item.rejectionReason" class="bg-red-50/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-3">
                                    <div class="flex items-start gap-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-700">
                                        <XCircle class="mt-0.5 size-3.5 shrink-0 text-red-500" />
                                        <span><span class="font-semibold">Alasan penolakan:</span> {{ item.rejectionReason }}</span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris konfirmasi batalkan -->
                            <tr v-if="cancelConfirmId === item.id" class="bg-amber-50/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-3">
                                    <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
                                        <p class="text-xs font-medium text-amber-800">Batalkan pengajuan ini?</p>
                                        <button type="button"
                                            class="rounded-lg bg-red-600 px-3 py-1 text-xs font-semibold text-white transition hover:bg-red-700"
                                            @click="cancelSurat(item.id)">Ya, Batalkan</button>
                                        <button type="button"
                                            class="rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                                            @click="cancelConfirmId = null">Tidak</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="surats.last_page > 1" class="mt-4 flex items-center justify-between">
            <p class="text-xs text-slate-400">Halaman {{ surats.current_page }} dari {{ surats.last_page }}</p>
            <div class="flex gap-1">
                <button type="button"
                    class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-40"
                    :disabled="surats.current_page === 1"
                    @click="goToPage(surats.current_page - 1)">
                    <ChevronLeft class="size-4" />
                </button>
                <button v-for="p in surats.last_page" :key="p" type="button"
                    class="grid size-8 place-items-center rounded-lg text-xs font-medium transition-colors"
                    :class="p === surats.current_page ? 'bg-emerald-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                    @click="goToPage(p)">{{ p }}</button>
                <button type="button"
                    class="grid size-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-40"
                    :disabled="surats.current_page === surats.last_page"
                    @click="goToPage(surats.current_page + 1)">
                    <ChevronRight class="size-4" />
                </button>
            </div>
        </div>

        <!-- Document Viewer Modal -->
        <Transition name="fade">
            <div v-if="viewerOpen"
                class="fixed inset-0 z-50 flex flex-col bg-black/70 backdrop-blur-sm"
                @click.self="closeViewer">

                <!-- Mode HTML: iframe -->
                <template v-if="viewerType === 'html'">
                    <div class="flex h-12 shrink-0 items-center justify-between bg-slate-900 px-4">
                        <p class="min-w-0 truncate text-sm font-medium text-white">{{ viewerTitle }}</p>
                        <button type="button"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-red-600 hover:text-white transition-colors"
                            @click="closeViewer">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex-1 overflow-auto bg-slate-800 p-4">
                        <iframe v-if="viewerUrl" :src="viewerUrl"
                            class="w-full rounded-lg border-0 bg-white shadow-2xl"
                            style="min-height: 80vh;" />
                    </div>
                </template>

                <!-- Mode PDF: PdfViewer -->
                <template v-else-if="viewerType === 'pdf' && viewerUrl">
                    <div class="flex h-9 shrink-0 items-center justify-between bg-slate-950 px-4">
                        <p class="min-w-0 truncate text-xs font-medium text-slate-400">{{ viewerTitle }}</p>
                        <button type="button"
                            class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1 text-xs text-slate-400 hover:bg-slate-800 hover:text-white transition-colors"
                            @click="closeViewer">
                            <X class="size-3.5" /> Tutup
                        </button>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <PdfViewer
                            :src="viewerUrl"
                            :filename="viewerTitle"
                            :show-thumbnails="false"
                            :initial-zoom="100"
                        />
                    </div>
                </template>
            </div>
        </Transition>
    </FastLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
