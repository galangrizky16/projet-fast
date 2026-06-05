<script setup lang="ts">
// resources/js/pages/admin/approval/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CheckCircle2, XCircle, Eye, Clock3, Filter } from 'lucide-vue-next';

type SuratItem = {
    id: number; status: string; nomor_surat?: string | null;
    keperluan?: string | null; tanggal_pengajuan?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
};

type Paginated = {
    data: SuratItem[];
    from?: number | null; to?: number | null; total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const props = defineProps<{
    surats: Paginated;
    summary: { menunggu: number; disetujui: number; ditolak: number };
    filters: { status?: string; search?: string };
    jenisSurats: Array<{ id: number; nama: string }>;
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

// Modal approve/reject
const showModal = ref(false);
const modalType = ref<'approve' | 'reject'>('approve');
const targetSuratId = ref<number | null>(null);
const targetSuratNama = ref('');

const actionForm = useForm({ catatan: '' });

function openModal(type: 'approve' | 'reject', surat: SuratItem) {
    modalType.value = type;
    targetSuratId.value = surat.id;
    targetSuratNama.value = surat.jenisSurat?.nama ?? 'Surat';
    actionForm.reset();
    showModal.value = true;
}

function submitAction() {
    if (!targetSuratId.value) return;
    const url = modalType.value === 'approve'
        ? `/approval/surat/${targetSuratId.value}/approve`
        : `/approval/surat/${targetSuratId.value}/reject`;

    actionForm.post(url, {
        onSuccess: () => { showModal.value = false; },
    });
}

function applyFilter() {
    router.get('/approval/dashboard', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true, replace: true });
}

function formatDate(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
    }).format(new Date(d));
}

function statusLabel(s: string) {
    const map: Record<string, string> = {
        validated_admin: 'Menunggu Approval',
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
    return 'bg-amber-100 text-amber-700';
}
</script>

<template>
    <AdminLayout
        title="Dashboard Approval"
        subtitle="Antrian surat yang menunggu persetujuan"
        active-menu="approval"
        :breadcrumbs="[{ label: 'Approval' }]"
    >
        <Head title="Dashboard Approval" />

        <!-- Stat cards -->
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div class="flex items-center gap-3">
                    <Clock3 class="size-8 text-amber-500" />
                    <div>
                        <p class="text-xs text-amber-600">Menunggu Approval</p>
                        <p class="text-3xl font-bold text-amber-800">{{ summary.menunggu }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <div class="flex items-center gap-3">
                    <CheckCircle2 class="size-8 text-emerald-500" />
                    <div>
                        <p class="text-xs text-emerald-600">Disetujui</p>
                        <p class="text-3xl font-bold text-emerald-800">{{ summary.disetujui }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                <div class="flex items-center gap-3">
                    <XCircle class="size-8 text-red-400" />
                    <div>
                        <p class="text-xs text-red-600">Ditolak</p>
                        <p class="text-3xl font-bold text-red-800">{{ summary.ditolak }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <!-- Filter -->
            <div class="flex flex-wrap gap-2 border-b border-slate-100 px-5 py-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama, NIM..."
                    class="h-8 w-44 rounded-lg border border-slate-200 bg-slate-50 px-3 text-xs outline-none focus:border-emerald-400"
                    @keyup.enter="applyFilter"
                />
                <select v-model="status" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs outline-none focus:border-emerald-400">
                    <option value="">Semua Status</option>
                    <option value="validated_admin">Menunggu Approval</option>
                    <option value="approved_kaprodi">Disetujui Kaprodi</option>
                    <option value="finished">Selesai</option>
                    <option value="rejected">Ditolak</option>
                </select>
                <button type="button" class="h-8 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="applyFilter">
                    Terapkan
                </button>
                <p class="ml-auto flex items-center text-xs text-slate-400">{{ surats.total }} surat</p>
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
                                Tidak ada surat dalam antrian approval.
                            </td>
                        </tr>
                        <tr
                            v-for="item in surats.data"
                            :key="item.id"
                            class="border-t border-slate-100 text-sm hover:bg-slate-50/50 transition-colors"
                        >
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-900">{{ item.pemohon?.name ?? '-' }}</p>
                                <p class="font-mono text-[10px] text-slate-400">{{ item.pemohon?.nim ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 max-w-[180px] truncate">
                                {{ item.jenisSurat?.nama ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">
                                {{ formatDate(item.tanggal_pengajuan) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold" :class="statusClass(item.status)">
                                    {{ statusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="`/approval/surat/${item.id}`"
                                        class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                                        title="Lihat detail">
                                        <Eye class="size-3.5" />
                                    </Link>
                                    <button
                                        v-if="item.status === 'validated_admin'"
                                        type="button"
                                        class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                        title="Setujui"
                                        @click="openModal('approve', item)"
                                    >
                                        <CheckCircle2 class="size-3.5" />
                                    </button>
                                    <button
                                        v-if="['validated_admin', 'approved_kaprodi'].includes(item.status)"
                                        type="button"
                                        class="grid size-7 place-items-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors"
                                        title="Tolak"
                                        @click="openModal('reject', item)"
                                    >
                                        <XCircle class="size-3.5" />
                                    </button>
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
                    :class="[link.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200', !link.url ? 'pointer-events-none opacity-40' : '']"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Modal Approve / Reject -->
        <Transition name="fade">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                @click.self="showModal = false"
            >
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ modalType === 'approve' ? 'Setujui Surat' : 'Tolak Surat' }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">{{ targetSuratNama }}</p>

                    <div class="mt-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">
                                {{ modalType === 'approve' ? 'Catatan (opsional)' : 'Alasan Penolakan *' }}
                            </span>
                            <textarea
                                v-model="actionForm.catatan"
                                rows="3"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400"
                                :placeholder="modalType === 'approve' ? 'Catatan tambahan...' : 'Jelaskan alasan penolakan...'"
                            />
                            <p v-if="actionForm.errors.catatan" class="text-xs text-red-500">{{ actionForm.errors.catatan }}</p>
                        </label>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors" @click="showModal = false">
                            Batal
                        </button>
                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 text-sm font-semibold text-white transition-colors disabled:opacity-50"
                            :class="modalType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-500 hover:bg-red-600'"
                            :disabled="actionForm.processing"
                            @click="submitAction"
                        >
                            {{ actionForm.processing ? 'Memproses...' : (modalType === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
