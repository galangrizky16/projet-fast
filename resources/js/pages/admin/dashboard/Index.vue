<script setup lang="ts">
// File: resources/js/pages/admin/dashboard/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { FilePlus2, Eye, Download, CheckCircle2, XCircle, Clock3, FileText, TrendingUp, Calendar, AlertTriangle, X, Pencil } from 'lucide-vue-next';

type SuratItem = {
    id: number; status: string; nomor_surat?: string | null;
    tanggal_pengajuan?: string | null; created_at?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
};

type HistoryItem = {
    id: number; action: string; action_label: string;
    keterangan?: string | null; created_at: string;
    user?: { name?: string | null } | null;
    surat?: { id: number; jenisSurat?: { nama?: string | null } | null } | null;
};

type Summary = { total: number; pending: number; validated: number; finished: number; rejected: number };
type PaginatedSurats = {
    data: SuratItem[]; from?: number | null; to?: number | null; total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    surats: PaginatedSurats;
    summary: Summary;
    recentHistory: HistoryItem[];
    filters: { status?: string; search?: string; jenis_surat_id?: string };
    jenisSurats: Array<{ id: number; nama: string }>;
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const jenisSuratId = ref(props.filters.jenis_surat_id ?? '');

function applyFilter() {
    router.get('/admin/dashboard', {
        search: search.value || undefined,
        status: status.value || undefined,
        jenis_surat_id: jenisSuratId.value || undefined,
    }, { preserveState: true, replace: true });
}

function resetFilter() {
    search.value = '';
    status.value = '';
    jenisSuratId.value = '';
    applyFilter();
}

// ── Aksi validasi admin (centang / silang / komen) ──────────────────────────
const approvingId = ref<number | null>(null);
function approveSurat(id: number) {
    if (approvingId.value) return;
    approvingId.value = id;
    router.post(`/admin/surat/${id}/approve`, {}, {
        preserveScroll: true,
        onFinish: () => { approvingId.value = null; },
    });
}

const rejectModalOpen = ref(false);
const rejectTargetId = ref<number | null>(null);
const rejectForm = useForm({ reason: '' });
function openRejectModal(id: number) {
    rejectTargetId.value = id;
    rejectForm.reset();
    rejectModalOpen.value = true;
}
function closeRejectModal() {
    rejectModalOpen.value = false;
    rejectTargetId.value = null;
}
function submitReject() {
    if (rejectTargetId.value === null) return;
    rejectForm.post(`/admin/surat/${rejectTargetId.value}/reject`, {
        preserveScroll: true,
        onSuccess: () => closeRejectModal(),
    });
}

const statCards = computed(() => [
    { label: 'Total Surat', value: props.summary.total, icon: FileText, color: 'indigo', delta: '+8 bulan ini' },
    { label: 'Menunggu Proses', value: props.summary.pending + props.summary.validated, icon: Clock3, color: 'amber', delta: 'perlu tindakan' },
    { label: 'Selesai', value: props.summary.finished, icon: CheckCircle2, color: 'emerald', delta: '+5 minggu ini' },
    { label: 'Ditolak', value: props.summary.rejected, icon: XCircle, color: 'red', delta: 'perlu revisi' },
]);

function statusLabel(s: string) {
    const map: Record<string, string> = {
        pending: 'Pending', validated_admin: 'Validasi Admin',
        approved_kaprodi: 'Disetujui Kaprodi', approved_dekan: 'Disetujui Dekan',
        finished: 'Selesai', rejected: 'Ditolak',
    };
    return map[s] ?? s;
}

// function statusClass(s: string) {
//     if (s === 'finished') return 'badge-green';
//     if (s === 'rejected') return 'badge-red';
//     if (s.startsWith('approved')) return 'badge-blue';
//     if (s === 'validated_admin') return 'badge-indigo';
//     return 'badge-amber';
// }

function statusClass(s: string) {
    if (s === 'finished') return 'bg-emerald-100 text-emerald-700';
    if (s === 'rejected') return 'bg-red-100 text-red-700';
    if (s.startsWith('approved')) return 'bg-blue-100 text-blue-700';
    if (s === 'validated_admin') return 'bg-indigo-100 text-indigo-700';
    return 'bg-amber-100 text-amber-700';
}

function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d));
}

function historyIcon(action: string) {
    if (action.includes('approved') || action === 'validated') return '✓';
    if (action === 'rejected') return '✗';
    if (action === 'generated' || action === 'printed') return '⊙';
    if (action === 'created') return '+';
    return '·';
}

function historyColor(action: string) {
    if (action.includes('approved') || action === 'validated') return 'text-emerald-600 bg-emerald-50';
    if (action === 'rejected') return 'text-red-600 bg-red-50';
    if (action === 'created') return 'text-indigo-600 bg-indigo-50';
    return 'text-slate-500 bg-slate-100';
}
</script>

<template>
    <AdminLayout
        title="Dashboard"
        subtitle="Monitoring pengajuan surat"
        active-menu="dashboard"
    >
        <Head title="Dashboard Admin" />

        <!-- Stat cards -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="rounded-2xl border border-slate-200 bg-white p-5"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-slate-400">{{ card.label }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ card.value }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ card.delta }}</p>
                    </div>
                    <div
                        class="rounded-xl p-2.5"
                        :class="{
                            'bg-indigo-50 text-indigo-600': card.color === 'indigo',
                            'bg-amber-50 text-amber-600': card.color === 'amber',
                            'bg-emerald-50 text-emerald-600': card.color === 'emerald',
                            'bg-red-50 text-red-600': card.color === 'red',
                        }"
                    >
                        <component :is="card.icon" class="size-5" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Main grid -->
        <div class="grid gap-6 xl:grid-cols-[1fr_300px]">

            <!-- Tabel surat -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <!-- Toolbar -->
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Daftar Pengajuan Surat</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ surats.from ?? 0 }}–{{ surats.to ?? 0 }} dari {{ surats.total }}</p>
                    </div>
                    <Link href="/admin/surat/create" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors shrink-0">
                        <FilePlus2 class="size-3.5" />
                        Buat Surat
                    </Link>
                </div>

                <!-- Filter -->
                <div class="flex flex-wrap gap-2 border-b border-slate-100 px-5 py-3">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama, NIM..."
                        class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100 w-40"
                        @keyup.enter="applyFilter"
                    />
                    <select v-model="status" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs text-slate-700 outline-none focus:border-emerald-400">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="validated_admin">Validasi Admin</option>
                        <option value="approved_kaprodi">Disetujui Kaprodi</option>
                        <option value="finished">Selesai</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <select v-model="jenisSuratId" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs text-slate-700 outline-none focus:border-emerald-400">
                        <option value="">Semua Jenis</option>
                        <option v-for="j in jenisSurats" :key="j.id" :value="String(j.id)">{{ j.nama }}</option>
                    </select>
                    <button type="button" class="h-8 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="applyFilter">Terapkan</button>
                    <button type="button" class="h-8 rounded-lg border border-slate-200 px-3 text-xs text-slate-500 hover:bg-slate-50 transition-colors" @click="resetFilter">Reset</button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                <th class="px-5 py-3">Pemohon</th>
                                <th class="px-5 py-3">Jenis Surat</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="surats.data.length === 0">
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">
                                    Belum ada data surat.
                                </td>
                            </tr>
                            <tr
                                v-for="item in surats.data"
                                :key="item.id"
                                class="border-t border-slate-100 text-sm hover:bg-slate-50/50 transition-colors"
                            >
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-900 text-xs">{{ item.pemohon?.name ?? '-' }}</p>
                                    <p class="font-mono text-[10px] text-slate-400">{{ item.pemohon?.nim ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600 max-w-[160px] truncate">
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ formatDate(item.tanggal_pengajuan ?? item.created_at) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="rounded-full px-2 py-1 text-[10px] font-semibold" :class="statusClass(item.status)">
                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/admin/surat/${item.id}`" title="Lihat detail" class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors">
                                            <Eye class="size-3.5" />
                                        </Link>
                                        <a v-if="item.status === 'finished'" :href="`/admin/surat/${item.id}/pdf`" target="_blank" title="Unduh PDF" class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors">
                                            <Download class="size-3.5" />
                                        </a>
                                        <button v-if="item.status === 'pending'" type="button" title="Validasi & teruskan" :disabled="approvingId === item.id" class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors disabled:opacity-50" @click="approveSurat(item.id)">
                                            <CheckCircle2 class="size-3.5" />
                                        </button>
                                        <button v-if="item.status === 'pending'" type="button" title="Tolak (beri komentar)" class="grid size-7 place-items-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" @click="openRejectModal(item.id)">
                                            <XCircle class="size-3.5" />
                                        </button>
                                        <Link v-if="item.status === 'rejected'" :href="`/admin/surat/${item.id}/edit`" title="Edit & teruskan" class="grid size-7 place-items-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                            <Pencil class="size-3.5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="surats.links.length > 3" class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3">
                    <Link
                        v-for="link in surats.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                        :class="[
                            link.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>

            <!-- Recent history -->
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Aktivitas Terbaru</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Riwayat aksi sistem</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <div v-if="recentHistory.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">
                        Belum ada aktivitas.
                    </div>
                    <div
                        v-for="h in recentHistory"
                        :key="h.id"
                        class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50/50 transition-colors"
                    >
                        <div class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full text-[10px] font-bold" :class="historyColor(h.action)">
                            {{ historyIcon(h.action) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-slate-900 truncate">{{ h.action_label }}</p>
                            <p v-if="h.surat?.jenisSurat?.nama" class="text-[10px] text-slate-400 truncate">{{ h.surat.jenisSurat.nama }}</p>
                            <p class="text-[10px] text-slate-400">{{ h.user?.name ?? 'Sistem' }} · {{ formatDate(h.created_at) }}</p>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-100 px-5 py-3">
                    <Link href="/admin/history" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                        Lihat semua riwayat →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modal Tolak Pengajuan (komentar) -->
        <Transition name="fade">
            <div v-if="rejectModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                @click.self="closeRejectModal">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-red-50">
                            <AlertTriangle class="size-5 text-red-500" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Tolak Pengajuan</h3>
                            <p class="text-xs text-slate-400">Berikan komentar/alasan penolakan untuk pemohon.</p>
                        </div>
                        <button type="button" class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-slate-100" @click="closeRejectModal">
                            <X class="size-4" />
                        </button>
                    </div>
                    <textarea
                        v-model="rejectForm.reason"
                        rows="4"
                        placeholder="Jelaskan alasan penolakan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100 resize-none"
                    />
                    <p v-if="rejectForm.errors.reason" class="mt-1 text-xs text-red-500">{{ rejectForm.errors.reason }}</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                            @click="closeRejectModal">Batal</button>
                        <button type="button"
                            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-50"
                            :disabled="rejectForm.processing || !rejectForm.reason.trim()"
                            @click="submitReject">
                            {{ rejectForm.processing ? 'Memproses...' : 'Tolak Pengajuan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<!-- <style scoped> //error
.badge-green  { @apply bg-emerald-100 text-emerald-700; }
.badge-red    { @apply bg-red-100 text-red-700; }
.badge-amber  { @apply bg-amber-100 text-amber-700; }
.badge-blue   { @apply bg-blue-100 text-blue-700; }
.badge-indigo { @apply bg-indigo-100 text-indigo-700; }
</style> -->

<!-- <style scoped>  //opsi pertama
@reference "tailwindcss";

.badge-green  { @apply bg-emerald-100 text-emerald-700; }
.badge-red    { @apply bg-red-100 text-red-700; }
.badge-amber  { @apply bg-amber-100 text-amber-700; }
.badge-blue   { @apply bg-blue-100 text-blue-700; }
.badge-indigo { @apply bg-indigo-100 text-indigo-700; }
</style> -->
