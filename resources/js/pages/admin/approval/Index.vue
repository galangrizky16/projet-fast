<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { BadgeCheck, Check, Clock3, Eye, MessageSquare, Search, ShieldCheck, X, XCircle } from 'lucide-vue-next';

type Summary = { waiting: number; approved: number; rejected: number };
type DetailLampiran = { id: number; name: string; url: string; type?: string | null };
type SuratItem = {
    id: number; status: string;
    tanggal_pengajuan?: string | null; created_at?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    jenisSurat?: { id?: number | null; nama?: string | null } | null;
};
type SuratDetail = {
    id: number; status: string; jenis_surat?: string | null;
    keperluan?: string | null; tanggal_pengajuan?: string | null;
    pemohon?: { name?: string | null; nim?: string | null } | null;
    isi_surat?: Record<string, unknown>;
    lampiran?: DetailLampiran[];
};
type PaginationLink = { url: string | null; label: string; active: boolean };
type PaginatedSurats = { data: SuratItem[]; links: PaginationLink[]; from?: number | null; to?: number | null; total: number };
type FilterState = { status?: string; search?: string; jenis_surat_id?: string };
type JenisSuratOption = { id: number; nama: string };
type PageProps = { auth: { user?: { name?: string } }; flash?: { success?: string } };

const props = defineProps<{
    role: { name?: string | null; slug?: string | null };
    surats: PaginatedSurats;
    summary: Summary;
    filters: FilterState;
    jenisSurats: JenisSuratOption[];
}>();

// const props = defineProps<{
//     surats: Paginated;
//     summary: { waiting: number; approved: number; rejected: number }; // Mengikuti penamaan backend
//     filters: { status?: string; search?: string };
//     jenisSurats: Array<{ id: number; nama: string }>;
// }>();

const page = usePage<PageProps>();
const filters = reactive({
    status: props.filters.status ?? 'validated_admin',
    search: props.filters.search ?? '',
    jenis_surat_id: props.filters.jenis_surat_id ?? '',
});
const detailModalOpen       = ref(false);
const detailLoading         = ref(false);
const detailData            = ref<SuratDetail | null>(null);
const attachmentPreviewOpen = ref(false);
const activeAttachment      = ref<DetailLampiran | null>(null);
const rejectModalOpen       = ref(false);
const noteModalOpen         = ref(false);
const selectedSurat         = ref<SuratItem | null>(null);
const toastMessage          = ref('');
const rejectForm            = useForm({ reason: '' });
const noteForm              = useForm({ catatan: '' });

const normalizedRole = computed(() =>
    String(props.role.slug ?? props.role.name ?? '').toLowerCase().includes('kaprodi') ? 'kaprodi' : 'dekan',
);

const statusOptions = computed(() => [
    { value: 'validated_admin', label: 'Menunggu Approval' },
    { value: normalizedRole.value === 'kaprodi' ? 'approved_kaprodi' : 'approved_dekan', label: 'Disetujui' },
    { value: 'rejected', label: 'Ditolak' },
]);

const summaryCards = computed(() => [
    { title: 'Menunggu Approval', value: props.summary.waiting,  icon: Clock3,    color: 'amber' },
    { title: 'Disetujui',         value: props.summary.approved, icon: BadgeCheck, color: 'emerald' },
    { title: 'Ditolak',           value: props.summary.rejected, icon: XCircle,   color: 'red' },
]);

watch(() => page.props.flash?.success, (message) => {
    if (typeof message === 'string' && message.length > 0) {
        toastMessage.value = message;
        window.setTimeout(() => { if (toastMessage.value === message) toastMessage.value = ''; }, 2800);
    }
}, { immediate: true });

const ns = (s?: string | null) => String(s ?? '').trim().toLowerCase();

function rowCanBeProcessed(item: SuratItem) { return ns(item.status) === 'validated_admin'; }

function statusLabel(status: string) {
    const s = ns(status);
    if (s === 'validated_admin') return 'Menunggu Approval';
    if (s === 'approved_kaprodi') return 'Disetujui Kaprodi';
    if (s === 'approved_dekan') return 'Disetujui Dekan';
    if (s === 'rejected') return 'Ditolak';
    return 'Diproses';
}

function statusBadgeClass(status: string) {
    const s = ns(status);
    if (s === 'validated_admin') return 'bg-amber-100 text-amber-700';
    if (s === 'approved_kaprodi' || s === 'approved_dekan') return 'bg-emerald-100 text-emerald-700';
    if (s === 'rejected') return 'bg-red-100 text-red-700';
    return 'bg-slate-100 text-slate-600';
}

function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(date));
}

function applyFilters() {
    router.get('/approval/dashboard',
        Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')),
        { preserveState: true, preserveScroll: true, replace: true }
    );
}

function resetFilters() { filters.status = 'validated_admin'; filters.search = ''; filters.jenis_surat_id = ''; applyFilters(); }
function submitApprove(item: SuratItem) { router.post(`/approval/surat/${item.id}/approve`, {}, { preserveScroll: true }); }

async function openDetail(id: number) {
    detailLoading.value = true; detailData.value = null;
    try {
        const r = await fetch(`/approval/surat/${id}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!r.ok) throw new Error('Gagal memuat detail surat.');
        detailData.value = await r.json();
        detailModalOpen.value = true;
    } catch (e) {
        toastMessage.value = e instanceof Error ? e.message : 'Gagal memuat detail.';
        window.setTimeout(() => { toastMessage.value = ''; }, 2800);
    } finally { detailLoading.value = false; }
}

function closeDetailModal()      { detailModalOpen.value = false; detailData.value = null; }
function openAttachmentPreview(f: DetailLampiran) { activeAttachment.value = f; attachmentPreviewOpen.value = true; }
function closeAttachmentPreview() { attachmentPreviewOpen.value = false; activeAttachment.value = null; }

function openRejectModal(item: SuratItem) {
    selectedSurat.value = item; rejectForm.reset(); rejectForm.clearErrors(); rejectModalOpen.value = true;
}
function closeRejectModal() { rejectModalOpen.value = false; selectedSurat.value = null; rejectForm.reset(); }
function submitReject() {
    if (!selectedSurat.value) return;
    rejectForm.post(`/approval/surat/${selectedSurat.value.id}/reject`, {
        preserveScroll: true, onSuccess: () => closeRejectModal(),
    });
}

function openNoteModal(item: SuratItem) {
    selectedSurat.value = item; noteForm.reset(); noteForm.clearErrors(); noteModalOpen.value = true;
}
function closeNoteModal() { noteModalOpen.value = false; selectedSurat.value = null; noteForm.reset(); }
function submitNote() {
    if (!selectedSurat.value) return;
    noteForm.post(`/approval/surat/${selectedSurat.value.id}/note`, {
        preserveScroll: true, onSuccess: () => closeNoteModal(),
    });
}

function detailEntries(payload?: Record<string, unknown>) {
    if (!payload) return [];
    const d = payload.data && typeof payload.data === 'object' && !Array.isArray(payload.data)
        ? payload.data as Record<string, unknown> : payload;
    const ignored = new Set(['jenis_surat_id','jenis_surat','keperluan','tanggal_kebutuhan','data']);
    return Object.entries(d).filter(([k, v]) => !ignored.has(k) && v !== null && v !== '');
}
function formatLabel(key: string) { return key.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase()); }
function formatDetailValue(v: unknown) { return Array.isArray(v) ? v.join(', ') : typeof v === 'object' && v ? JSON.stringify(v) : String(v); }
function isImageAttachment(f?: DetailLampiran | null) {
    if (!f) return false;
    return (f.type??'').toLowerCase().startsWith('image/') || ['.jpg','.jpeg','.png','.gif','.webp'].some(e => f.name.toLowerCase().endsWith(e));
}
function isPdfAttachment(f?: DetailLampiran | null) {
    if (!f) return false;
    return (f.type??'').toLowerCase().includes('pdf') || f.name.toLowerCase().endsWith('.pdf');
}
</script>

<template>
    <AdminLayout
        :title="`Dashboard ${role.name || 'Approval'}`"
        subtitle="Approval lanjutan setelah validasi admin"
        active-menu="approval"
        :breadcrumbs="[{ label: 'Approval' }]"
    >
        <Head :title="`Dashboard ${role.name || 'Approval'}`" />

        <!-- Stat cards -->
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div v-for="card in summaryCards" :key="card.title"
                class="rounded-2xl border bg-white p-5"
                :class="{
                    'border-amber-200 bg-amber-50': card.color==='amber',
                    'border-emerald-200 bg-emerald-50': card.color==='emerald',
                    'border-red-200 bg-red-50': card.color==='red',
                }">
                <div class="flex items-center gap-3">
                    <component :is="card.icon" class="size-8"
                        :class="{'text-amber-500':card.color==='amber','text-emerald-500':card.color==='emerald','text-red-400':card.color==='red'}" />
                    <div>
                        <p class="text-xs font-medium"
                            :class="{'text-amber-600':card.color==='amber','text-emerald-600':card.color==='emerald','text-red-600':card.color==='red'}">
                            {{ card.title }}
                        </p>
                        <p class="text-3xl font-bold"
                            :class="{'text-amber-800':card.color==='amber','text-emerald-800':card.color==='emerald','text-red-800':card.color==='red'}">
                            {{ card.value }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel -->
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <!-- Header + Filter -->
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Daftar Surat Approval</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ surats.from ?? 0 }}–{{ surats.to ?? 0 }} dari {{ surats.total }} data</p>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                        <ShieldCheck class="size-4 text-emerald-500" />
                        <span class="text-xs font-semibold text-slate-700">{{ role.name }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div class="relative">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                        <input v-model="filters.search" type="text" placeholder="Cari nama atau NIM..."
                            class="h-9 w-52 rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 placeholder-slate-400 outline-none focus:border-emerald-400"
                            @keyup.enter="applyFilters" />
                    </div>
                    <select v-model="filters.status" class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400">
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <select v-model="filters.jenis_surat_id" class="h-9 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400">
                        <option value="">Semua Jenis Surat</option>
                        <option v-for="j in jenisSurats" :key="j.id" :value="String(j.id)">{{ j.nama }}</option>
                    </select>
                    <button type="button" class="h-9 rounded-xl bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="applyFilters">Terapkan</button>
                    <button type="button" class="h-9 rounded-xl border border-slate-200 px-4 text-xs text-slate-500 hover:bg-slate-50 transition-colors" @click="resetFilters">Reset</button>
                </div>
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
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada data approval untuk filter yang dipilih.</td>
                        </tr>
                        <tr v-for="item in surats.data" :key="item.id"
                            class="border-t border-slate-100 text-sm hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-900">{{ item.pemohon?.name || '-' }}</p>
                                <p class="font-mono text-[10px] text-slate-400">{{ item.pemohon?.nim || '-' }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 max-w-[180px] truncate">{{ item.jenisSurat?.nama || '-' }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">{{ formatDate(item.tanggal_pengajuan || item.created_at) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2 py-1 text-[10px] font-semibold" :class="statusBadgeClass(item.status)">
                                    {{ statusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" title="Lihat Detail"
                                        class="grid size-7 place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors"
                                        @click="openDetail(item.id)">
                                        <Eye class="size-3.5" />
                                    </button>
                                    <button type="button" title="Tambah Catatan"
                                        class="grid size-7 place-items-center rounded-lg bg-indigo-50 text-indigo-500 hover:bg-indigo-100 transition-colors"
                                        @click="openNoteModal(item)">
                                        <MessageSquare class="size-3.5" />
                                    </button>
                                    <template v-if="rowCanBeProcessed(item)">
                                        <button type="button" title="Setujui"
                                            class="grid size-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors"
                                            @click="submitApprove(item)">
                                            <Check class="size-3.5" />
                                        </button>
                                        <button type="button" title="Tolak"
                                            class="grid size-7 place-items-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-colors"
                                            @click="openRejectModal(item)">
                                            <X class="size-3.5" />
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button disabled class="grid size-7 cursor-not-allowed place-items-center rounded-lg bg-slate-100 text-slate-300 opacity-50"><Check class="size-3.5" /></button>
                                        <button disabled class="grid size-7 cursor-not-allowed place-items-center rounded-lg bg-slate-100 text-slate-300 opacity-50"><X class="size-3.5" /></button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="surats.links.length > 3" class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 px-5 py-3">
                <Link v-for="link in surats.links" :key="`${link.label}-${link.url}`"
                    :href="link.url || ''"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                    :class="[link.active?'bg-emerald-600 text-white':'bg-slate-100 text-slate-600 hover:bg-slate-200', !link.url?'pointer-events-none opacity-40':'']"
                    v-html="link.label" />
            </div>
        </div>

        <!-- Modal Detail -->
        <Dialog :open="detailModalOpen" @update:open="v => v ? null : closeDetailModal()">
            <DialogContent class="flex max-h-[90vh] w-[min(860px,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border-0 bg-white p-0" :show-close-button="false">
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader class="text-left">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <DialogTitle class="text-lg font-semibold text-slate-900">Detail Surat</DialogTitle>
                                <DialogDescription class="text-sm text-slate-400">Tinjau detail pengajuan secara lengkap.</DialogDescription>
                            </div>
                            <span v-if="detailData" class="rounded-full px-2.5 py-1 text-[10px] font-semibold" :class="statusBadgeClass(detailData.status)">{{ statusLabel(detailData.status) }}</span>
                        </div>
                    </DialogHeader>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                    <div v-if="detailLoading" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">Memuat...</div>
                    <div v-else-if="detailData" class="space-y-5">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><p class="text-xs text-slate-400">Nama</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ detailData.pemohon?.name || '-' }}</p></div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><p class="text-xs text-slate-400">NIM / NIP</p><p class="mt-1 font-mono text-sm font-semibold text-slate-800">{{ detailData.pemohon?.nim || '-' }}</p></div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><p class="text-xs text-slate-400">Jenis Surat</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ detailData.jenis_surat || '-' }}</p></div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4"><p class="text-xs text-slate-400">Tanggal</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ formatDate(detailData.tanggal_pengajuan) }}</p></div>
                            <div class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 p-4"><p class="text-xs text-slate-400">Keperluan</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ detailData.keperluan || '-' }}</p></div>
                        </div>
                        <div v-if="detailEntries(detailData.isi_surat).length > 0">
                            <h3 class="mb-3 text-sm font-semibold text-slate-800">Isi Surat</h3>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div v-for="[key, value] in detailEntries(detailData.isi_surat)" :key="key"
                                    class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-xs text-slate-400">{{ formatLabel(key) }}</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ formatDetailValue(value) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="detailData.lampiran && detailData.lampiran.length > 0">
                            <h3 class="mb-3 text-sm font-semibold text-slate-800">Lampiran</h3>
                            <div class="space-y-2">
                                <button v-for="file in detailData.lampiran" :key="file.id" type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left hover:border-emerald-300 hover:bg-emerald-50 transition-colors"
                                    @click="openAttachmentPreview(file)">
                                    <div><p class="text-sm font-medium text-slate-800">{{ file.name }}</p><p class="text-xs text-slate-400">{{ file.type || 'File pendukung' }}</p></div>
                                    <span class="text-xs font-semibold text-emerald-600">Preview</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                    <Button variant="ghost" @click="closeDetailModal">Tutup</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Modal Reject -->
        <Dialog :open="rejectModalOpen" @update:open="v => v ? null : closeRejectModal()">
            <DialogContent class="max-w-md rounded-2xl border-0 bg-white p-0" :show-close-button="false">
                <div class="p-6">
                    <DialogHeader class="text-left mb-4">
                        <DialogTitle class="text-lg font-semibold text-slate-900">Tolak Pengajuan</DialogTitle>
                        <DialogDescription class="text-sm text-slate-400">Isi alasan penolakan untuk {{ selectedSurat?.pemohon?.name || 'pemohon' }}.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitReject" class="space-y-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Alasan Penolakan <span class="text-red-500">*</span></span>
                            <textarea v-model="rejectForm.reason" rows="4"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                                placeholder="Jelaskan alasan penolakan..." />
                            <p v-if="rejectForm.errors.reason" class="text-xs text-red-500">{{ rejectForm.errors.reason }}</p>
                        </label>
                        <DialogFooter class="gap-2">
                            <Button type="button" variant="outline" class="rounded-xl" @click="closeRejectModal">Batal</Button>
                            <Button type="submit" class="rounded-xl bg-red-600 text-white hover:bg-red-700" :disabled="rejectForm.processing">Tolak Pengajuan</Button>
                        </DialogFooter>
                    </form>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Modal Catatan -->
        <Dialog :open="noteModalOpen" @update:open="v => v ? null : closeNoteModal()">
            <DialogContent class="max-w-md rounded-2xl border-0 bg-white p-0" :show-close-button="false">
                <div class="p-6">
                    <DialogHeader class="text-left mb-4">
                        <DialogTitle class="text-lg font-semibold text-slate-900">Tambah Catatan</DialogTitle>
                        <DialogDescription class="text-sm text-slate-400">
                            Catatan untuk {{ selectedSurat?.pemohon?.name || 'pemohon' }}. Mahasiswa dapat melihat catatan ini.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitNote" class="space-y-4">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Isi Catatan <span class="text-red-500">*</span></span>
                            <textarea v-model="noteForm.catatan" rows="4"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                                placeholder="Tulis catatan atau masukan untuk mahasiswa..." />
                            <p v-if="noteForm.errors.catatan" class="text-xs text-red-500">{{ noteForm.errors.catatan }}</p>
                        </label>
                        <DialogFooter class="gap-2">
                            <Button type="button" variant="outline" class="rounded-xl" @click="closeNoteModal">Batal</Button>
                            <Button type="submit" class="rounded-xl bg-indigo-600 text-white hover:bg-indigo-700"
                                :disabled="noteForm.processing || !noteForm.catatan">
                                {{ noteForm.processing ? 'Menyimpan...' : 'Simpan Catatan' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Modal Lampiran -->
        <Dialog :open="attachmentPreviewOpen" @update:open="v => v ? null : closeAttachmentPreview()">
            <DialogContent class="flex max-h-[90vh] w-[min(860px,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border-0 bg-white p-0" :show-close-button="false">
                <div class="border-b border-slate-100 px-6 py-4">
                    <DialogHeader class="text-left">
                        <DialogTitle class="text-lg font-semibold text-slate-900">Preview Lampiran</DialogTitle>
                        <DialogDescription class="text-sm text-slate-400">{{ activeAttachment?.name }}</DialogDescription>
                    </DialogHeader>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 p-4">
                    <div v-if="activeAttachment && isImageAttachment(activeAttachment)" class="flex justify-center">
                        <img :src="activeAttachment.url" :alt="activeAttachment.name" class="max-h-[65vh] rounded-xl border border-slate-200 object-contain shadow-sm" />
                    </div>
                    <div v-else-if="activeAttachment && isPdfAttachment(activeAttachment)" class="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                        <iframe :src="activeAttachment.url" class="h-[65vh] w-full" title="Preview PDF" />
                    </div>
                    <div v-else class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Preview hanya tersedia untuk PDF dan gambar.</div>
                </div>
                <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                    <Button variant="ghost" @click="closeAttachmentPreview">Tutup</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Toast -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-3 opacity-0">
            <div v-if="toastMessage"
                class="fixed left-1/2 top-5 z-50 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-xl border border-emerald-200 bg-emerald-600 px-4 py-3 text-white shadow-lg">
                <div class="flex items-center gap-2.5">
                    <BadgeCheck class="size-5 shrink-0" />
                    <p class="text-sm font-medium">{{ toastMessage }}</p>
                </div>
            </div>
        </Transition>

    </AdminLayout>
</template>