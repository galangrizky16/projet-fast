<script setup lang="ts">
// resources/js/pages/admin/letters/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FilePlus2, Eye, Download, CheckCircle2, XCircle, Search, Filter } from 'lucide-vue-next';

type SuratItem = {
    id: number; status: string;
    nomor_surat?: string | null; keperluan?: string | null;
    tanggal_pengajuan?: string | null; created_at?: string | null;
    tanggal_selesai?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null; category?: { nama?: string | null } | null } | null;
};

type Paginated = {
    data: SuratItem[];
    from?: number | null; to?: number | null; total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

type Summary = { total: number; pending: number; finished: number; rejected: number };

const props = defineProps<{
    surats: Paginated;
    summary: Summary;
    filters: { status?: string; search?: string; jenis_surat_id?: string };
    jenisSurats: Array<{ id: number; nama: string }>;
}>();

const search       = ref(props.filters.search ?? '');
const status       = ref(props.filters.status ?? '');
const jenisSuratId = ref(props.filters.jenis_surat_id ?? '');

function applyFilter() {
    router.get('/admin/surat', {
        search:        search.value || undefined,
        status:        status.value || undefined,
        jenis_surat_id: jenisSuratId.value || undefined,
    }, { preserveState: true, replace: true });
}

function resetFilter() {
    search.value = ''; status.value = ''; jenisSuratId.value = '';
    applyFilter();
}

function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d));
}

function statusLabel(s: string) {
    const map: Record<string, string> = {
        pending: 'Pending', validated_admin: 'Validasi Admin',
        approved_kaprodi: 'Disetujui Kaprodi', approved_dekan: 'Disetujui Dekan',
        finished: 'Selesai', rejected: 'Ditolak',
    };
    return map[s] ?? s;
}

function statusClass(s: string) {
    if (s === 'finished') return 'bg-emerald-100 text-emerald-700';
    if (s === 'rejected') return 'bg-red-100 text-red-700';
    if (s.startsWith('approved')) return 'bg-blue-100 text-blue-700';
    if (s === 'validated_admin') return 'bg-indigo-100 text-indigo-700';
    return 'bg-amber-100 text-amber-700';
}
</script>

<template>
    <AdminLayout
        title="Semua Pengajuan"
        subtitle="Daftar seluruh surat yang pernah dibuat"
        active-menu="letters.index"
        :breadcrumbs="[{ label: 'Semua Pengajuan' }]"
    >
        <template #actions>
            <Link href="/admin/surat/create"
                class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                <FilePlus2 class="size-3.5" /> Buat Surat
            </Link>
        </template>

        <Head title="Semua Pengajuan" />

        <!-- Stat cards -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="card in [
                { label: 'Total Surat',    value: summary.total,    color: 'indigo' },
                { label: 'Pending',        value: summary.pending,  color: 'amber' },
                { label: 'Selesai',        value: summary.finished, color: 'emerald' },
                { label: 'Ditolak',        value: summary.rejected, color: 'red' },
            ]" :key="card.label"
                class="rounded-2xl border bg-white p-5"
                :class="{
                    'border-indigo-200': card.color === 'indigo',
                    'border-amber-200':  card.color === 'amber',
                    'border-emerald-200': card.color === 'emerald',
                    'border-red-200':    card.color === 'red',
                }">
                <p class="text-xs font-medium text-slate-400">{{ card.label }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ card.value }}</p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">

            <!-- Filter -->
            <div class="flex flex-wrap gap-2 border-b border-slate-100 px-5 py-4">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Cari nama, NIM, nomor surat..."
                        class="h-9 w-56 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs outline-none focus:border-emerald-400"
                        @keyup.enter="applyFilter" />
                </div>
                <select v-model="status"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs outline-none focus:border-emerald-400">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="validated_admin">Validasi Admin</option>
                    <option value="approved_kaprodi">Disetujui Kaprodi</option>
                    <option value="approved_dekan">Disetujui Dekan</option>
                    <option value="finished">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <select v-model="jenisSuratId"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs outline-none focus:border-emerald-400">
                    <option value="">Semua Jenis</option>
                    <option v-for="j in jenisSurats" :key="j.id" :value="String(j.id)">{{ j.nama }}</option>
                </select>
                <button type="button"
                    class="h-9 rounded-xl bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors"
                    @click="applyFilter">
                    Terapkan
                </button>
                <button type="button"
                    class="h-9 rounded-xl border border-slate-200 px-4 text-xs text-slate-500 hover:bg-slate-50 transition-colors"
                    @click="resetFilter">
                    Reset
                </button>
                <p class="ml-auto flex items-center text-xs text-slate-400">
                    {{ surats.from ?? 0 }}–{{ surats.to ?? 0 }} dari {{ surats.total }} surat
                </p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            <th class="px-5 py-3">Pemohon</th>
                            <th class="px-5 py-3">Jenis Surat</th>
                            <th class="px-5 py-3">Nomor Surat</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="surats.data.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                                Belum ada data surat.
                            </td>
                        </tr>
                        <tr v-for="item in surats.data" :key="item.id"
                            class="border-t border-slate-100 text-sm hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-900">{{ item.pemohon?.name ?? '-' }}</p>
                                <p class="font-mono text-[10px] text-slate-400">{{ item.pemohon?.nim ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-medium text-slate-700 max-w-[150px] truncate">{{ item.jenisSurat?.nama ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400">{{ item.jenisSurat?.category?.nama ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500">
                                {{ item.nomor_surat ?? '-' }}
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
                                    <Link :href="`/admin/surat/${item.id}`"
                                        class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                                        title="Lihat Detail">
                                        <Eye class="size-3.5" />
                                    </Link>
                                    <a v-if="item.status === 'finished'"
                                        :href="`/admin/surat/${item.id}/pdf`"
                                        target="_blank"
                                        class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                        title="Download PDF">
                                        <Download class="size-3.5" />
                                    </a>
                                    <Link v-if="['pending','validated_admin'].includes(item.status)"
                                        :href="`/admin/surat/${item.id}`"
                                        class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                        title="Proses">
                                        <CheckCircle2 class="size-3.5" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="surats.links.length > 3" class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3">
                <Link v-for="link in surats.links" :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="[
                        link.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                        !link.url ? 'pointer-events-none opacity-40' : '',
                    ]"
                    v-html="link.label" />
            </div>
        </div>
    </AdminLayout>
</template>
