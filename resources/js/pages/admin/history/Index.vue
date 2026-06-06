<script setup lang="ts">
// File: resources/js/pages/admin/history/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Search,
    Eye,
    Download,
    CheckCircle2,
    XCircle,
    Clock3,
    FileText,
} from 'lucide-vue-next';
import { useRolePrefix } from '@/composables/useRolePrefix';

const { rolePrefix } = useRolePrefix();

type SuratItem = {
    id: number;
    nomor_surat?: string | null;
    status: string;
    keperluan: string;
    tanggal_pengajuan?: string | null;
    tanggal_selesai?: string | null;
    pemohon?: { name?: string | null } | null;
    jenisSurat?: { nama?: string | null } | null;
};

type Paginated = {
    data: SuratItem[];
    from?: number | null;
    to?: number | null;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    surats: Paginated;
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

function applyFilter() {
    router.get(
        `${rolePrefix.value}/history`,
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(d));
}

function statusLabel(s: string) {
    const map: Record<string, string> = {
        pending: 'Pending',
        validated_admin: 'Validasi Admin',
        approved_kaprodi: 'Disetujui Kaprodi',
        approved_dekan: 'Disetujui Dekan',
        finished: 'Selesai',
        rejected: 'Ditolak',
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
        title="Riwayat Surat"
        subtitle="Surat yang dibuat oleh admin"
        active-menu="history"
        :breadcrumbs="[{ label: 'Riwayat Surat' }]"
    >
        <Head title="Riwayat Surat" />

        <!-- Filter bar -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap gap-3">
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari keperluan, nomor surat..."
                        class="h-9 w-64 rounded-xl border border-slate-200 bg-slate-50 pr-3 pl-9 text-xs text-slate-700 outline-none focus:border-emerald-400"
                        @keyup.enter="applyFilter"
                    />
                </div>
                <select
                    v-model="status"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400"
                >
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="validated_admin">Validasi Admin</option>
                    <option value="approved_kaprodi">Disetujui Kaprodi</option>
                    <option value="approved_dekan">Disetujui Dekan</option>
                    <option value="finished">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <button
                    type="button"
                    class="h-9 rounded-xl bg-emerald-600 px-4 text-xs font-semibold text-white transition-colors hover:bg-emerald-700"
                    @click="applyFilter"
                >
                    Terapkan
                </button>
                <button
                    type="button"
                    class="h-9 rounded-xl border border-slate-200 px-4 text-xs text-slate-500 transition-colors hover:bg-slate-50"
                    @click="
                        search = '';
                        status = '';
                        applyFilter();
                    "
                >
                    Reset
                </button>
                <p class="ml-auto flex items-center text-xs text-slate-400">
                    {{ surats.total }} data
                </p>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr
                            class="text-[10px] font-semibold tracking-widest text-slate-400 uppercase"
                        >
                            <th class="px-5 py-3">Jenis Surat</th>
                            <th class="px-5 py-3">Keperluan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="surats.data.length === 0">
                            <td
                                colspan="5"
                                class="px-5 py-12 text-center text-sm text-slate-400"
                            >
                                Belum ada surat yang dibuat.
                            </td>
                        </tr>
                        <tr
                            v-for="item in surats.data"
                            :key="item.id"
                            class="border-t border-slate-100 text-sm transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-900">
                                    {{ item.jenisSurat?.nama ?? '-' }}
                                </p>
                                <p
                                    v-if="item.nomor_surat"
                                    class="font-mono text-[10px] text-slate-400"
                                >
                                    {{ item.nomor_surat }}
                                </p>
                            </td>
                            <td
                                class="max-w-[200px] truncate px-5 py-3.5 text-xs text-slate-600"
                            >
                                {{ item.keperluan }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="rounded-full px-2 py-1 text-[10px] font-semibold"
                                    :class="statusClass(item.status)"
                                >
                                    {{ statusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">
                                {{ formatDate(item.tanggal_pengajuan) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Link
                                        :href="`${rolePrefix}/surat/${item.id}`"
                                        title="Lihat detail"
                                        class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200"
                                    >
                                        <Eye class="size-3.5" />
                                    </Link>
                                    <a
                                        v-if="item.status === 'finished'"
                                        :href="`${rolePrefix}/surat/${item.id}/pdf`"
                                        target="_blank"
                                        title="Unduh PDF"
                                        class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600 transition-colors hover:bg-indigo-100"
                                    >
                                        <Download class="size-3.5" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="surats.links.length > 3"
                class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3"
            >
                <Link
                    v-for="link in surats.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="[
                        link.active
                            ? 'bg-emerald-600 text-white'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                        !link.url ? 'pointer-events-none opacity-40' : '',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AdminLayout>
</template>
