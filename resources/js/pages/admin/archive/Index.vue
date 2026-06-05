<script setup lang="ts">
// resources/js/pages/admin/archive/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, Download, Eye, Archive, FileText } from 'lucide-vue-next';

type SuratItem = {
    id: number;
    nomor_surat?: string | null;
    keperluan?: string | null;
    tanggal_selesai?: string | null;
    generated_file_path?: string | null;
    download_url?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { nama?: string | null } | null;
};

type Paginated = {
    data: SuratItem[];
    from?: number | null; to?: number | null; total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    surats: Paginated;
    filters: { search?: string; date_from?: string; date_to?: string; jenis_surat_id?: string };
    jenisSurats: Array<{ id: number; nama: string }>;
}>();

const search       = ref(props.filters.search ?? '');
const dateFrom     = ref(props.filters.date_from ?? '');
const dateTo       = ref(props.filters.date_to ?? '');
const jenisSuratId = ref(props.filters.jenis_surat_id ?? '');

function applyFilter() {
    router.get('/admin/archive', {
        search:         search.value || undefined,
        date_from:      dateFrom.value || undefined,
        date_to:        dateTo.value || undefined,
        jenis_surat_id: jenisSuratId.value || undefined,
    }, { preserveState: true, replace: true });
}

function resetFilter() {
    search.value = ''; dateFrom.value = ''; dateTo.value = ''; jenisSuratId.value = '';
    applyFilter();
}

function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
    }).format(new Date(d));
}
</script>

<template>
    <AdminLayout
        title="Arsip & Unduh"
        subtitle="Surat yang sudah selesai dan siap diunduh"
        active-menu="archive"
        :breadcrumbs="[{ label: 'Arsip & Unduh' }]"
    >
        <Head title="Arsip & Unduh" />

        <!-- Tabel -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">

            <!-- Filter -->
            <div class="flex flex-wrap gap-2 border-b border-slate-100 px-5 py-4">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                    <input v-model="search" type="text" placeholder="Cari nomor surat, nama..."
                        class="h-9 w-52 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-emerald-400"
                        @keyup.enter="applyFilter" />
                </div>
                <select v-model="jenisSuratId"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400">
                    <option value="">Semua Jenis</option>
                    <option v-for="j in jenisSurats" :key="j.id" :value="String(j.id)">{{ j.nama }}</option>
                </select>
                <input v-model="dateFrom" type="date"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" />
                <span class="flex items-center text-xs text-slate-400">s/d</span>
                <input v-model="dateTo" type="date"
                    class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" />
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

            <!-- Empty state -->
            <div v-if="surats.data.length === 0" class="flex flex-col items-center gap-3 py-16 text-center">
                <Archive class="size-12 text-slate-200" />
                <p class="text-sm text-slate-400">Belum ada surat yang selesai.</p>
                <p class="text-xs text-slate-300">Surat yang sudah di-generate PDF akan muncul di sini.</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                            <th class="px-5 py-3">Pemohon</th>
                            <th class="px-5 py-3">Jenis Surat</th>
                            <th class="px-5 py-3">Nomor Surat</th>
                            <th class="px-5 py-3">Keperluan</th>
                            <th class="px-5 py-3">Tanggal Selesai</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in surats.data" :key="item.id"
                            class="border-t border-slate-100 text-sm hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-900">{{ item.pemohon?.name ?? '-' }}</p>
                                <p class="font-mono text-[10px] text-slate-400">{{ item.pemohon?.nim ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 max-w-[150px] truncate">
                                {{ item.jenisSurat?.nama ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500">
                                {{ item.nomor_surat ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 max-w-[150px] truncate">
                                {{ item.keperluan ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">
                                {{ formatDate(item.tanggal_selesai) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="`/admin/surat/${item.id}`"
                                        class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                                        title="Lihat Detail">
                                        <Eye class="size-3.5" />
                                    </Link>
                                    <a v-if="item.download_url"
                                        :href="item.download_url"
                                        target="_blank"
                                        class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                        title="Download PDF">
                                        <Download class="size-3.5" />
                                    </a>
                                    <div v-else
                                        class="grid size-7 place-items-center rounded-lg bg-slate-50 text-slate-300"
                                        title="PDF belum tersedia">
                                        <FileText class="size-3.5" />
                                    </div>
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
