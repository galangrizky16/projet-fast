<script setup lang="ts">
// resources/js/pages/admin/dashboard/Show.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import PdfViewer from '@/components/PdfViewer.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, Download, Eye, ArrowLeft, Clock, CheckCircle, XCircle, QrCode, X, ShieldCheck, AlertTriangle, FileEdit } from 'lucide-vue-next';

type Lampiran = { id: number; name: string; url: string; type: string };
type Surat = {
    id: number;
    nomor_surat?: string|null;
    pemohon: { name: string; nim?: string|null };
    jenis_surat: string;
    keperluan: string;
    isi_surat: Record<string, any>;
    lampiran: Lampiran[];
    tanggal_pengajuan: string|null;
    status: string;
    can_approve: boolean;
    can_edit: boolean;
    previewTemplateUrl: string;
    generatedDocumentUrl: string|null;
};

const props = defineProps<{ id: number } & Surat>();

// ── PDF Viewer state ────────────────────────────────────────────────────────
const viewerOpen  = ref(false);
const viewerUrl   = ref<string | null>(null);
const viewerTitle = ref('');
const viewerType  = ref<'html' | 'pdf'>('html');
const isFinished  = computed(() => props.status === 'finished');

// ── Validasi Admin ──────────────────────────────────────────────────────────
const rejectModalOpen = ref(false);
const rejectForm = useForm({ reason: '' });

function approveAdmin() {
    router.post(`/admin/surat/${props.id}/approve`, {}, {
        onSuccess: () => {},
    });
}

function openRejectModal() { rejectModalOpen.value = true; rejectForm.reset(); }
function closeRejectModal() { rejectModalOpen.value = false; }
function submitReject() {
    rejectForm.post(`/admin/surat/${props.id}/reject`, {
        onSuccess: () => closeRejectModal(),
    });
}

function openPreviewTemplate() {
    viewerUrl.value   = props.previewTemplateUrl;
    viewerTitle.value = `Preview Template — ${props.jenis_surat}`;
    viewerType.value  = 'html';
    viewerOpen.value  = true;
}

function openPreviewDocument() {
    if (!props.generatedDocumentUrl) return;
    viewerUrl.value   = props.generatedDocumentUrl;
    viewerTitle.value = `${props.jenis_surat} — ${props.nomor_surat ?? ''}`;
    viewerType.value  = 'html';
    viewerOpen.value  = true;
}

function openDownloadPdf() {
    viewerUrl.value   = `/admin/surat/${props.id}/pdf`;
    viewerTitle.value = `${props.jenis_surat} — ${props.nomor_surat ?? ''}`;
    viewerType.value  = 'pdf';
    viewerOpen.value  = true;
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => { viewerUrl.value = null; }, 200);
}
// ── [END BARU] ──────────────────────────────────────────────────────────────

const statusLabel: Record<string, string> = {
    pending:            'Menunggu Validasi',
    validated_admin:    'Sudah Divalidasi Admin',
    approved_kaprodi:   'Disetujui Kaprodi',
    approved_dekan:     'Disetujui Dekan',
    finished:           'Selesai',
    rejected:           'Ditolak',
};
const statusColor: Record<string, string> = {
    pending:            'bg-amber-50 text-amber-700 border-amber-200',
    validated_admin:    'bg-blue-50 text-blue-700 border-blue-200',
    approved_kaprodi:   'bg-emerald-50 text-emerald-700 border-emerald-200',
    approved_dekan:     'bg-emerald-50 text-emerald-700 border-emerald-200',
    finished:           'bg-emerald-100 text-emerald-800 border-emerald-300',
    rejected:           'bg-red-50 text-red-700 border-red-200',
};

function formatDate(iso: string|null): string {
    if (!iso) return '-';
    return new Date(iso).toLocaleString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }) + ' WIB';
}
</script>

<template>
    <AdminLayout
        title="Detail Surat"
        :subtitle="jenis_surat"
        active-menu="letters"
        :breadcrumbs="[
            { label: 'Arsip', href: '/admin/archive' },
            { label: 'Detail Surat' },
        ]"
    >
        <Head :title="`Detail Surat — ${jenis_surat}`" />
        <div class="max-w-3xl mx-auto space-y-4">

            <!-- Tombol kembali -->
            <div>
                <button type="button"
                    class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                    @click="router.visit('/admin/archive')">
                    <ArrowLeft class="size-4" /> Kembali ke Arsip
                </button>
            </div>

            <!-- Header info -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Jenis Surat</p>
                        <h1 class="text-xl font-bold text-slate-900">{{ jenis_surat }}</h1>
                        <p v-if="nomor_surat" class="mt-1 font-mono text-sm text-slate-600">{{ nomor_surat }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border px-3 py-1 text-xs font-semibold"
                        :class="statusColor[status] ?? 'bg-slate-50 text-slate-700 border-slate-200'">
                        {{ statusLabel[status] ?? status }}
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-slate-400">Pemohon</p>
                        <p class="font-medium text-slate-800">{{ pemohon.name }}</p>
                        <p v-if="pemohon.nim" class="text-xs text-slate-500">{{ pemohon.nim }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Tanggal Pengajuan</p>
                        <p class="font-medium text-slate-800">{{ formatDate(tanggal_pengajuan) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-slate-400">Keperluan</p>
                        <p class="font-medium text-slate-800">{{ keperluan }}</p>
                    </div>
                </div>
            </div>

            <!-- Data isi surat -->
            <div v-if="Object.keys(isi_surat).length > 0" class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">Data Surat</h2>
                <div class="space-y-2">
                    <template v-for="(val, key) in isi_surat" :key="key">
                        <div v-if="!['created_by','jenis_surat_id','jenis_surat','keperluan'].includes(String(key))"
                            class="flex gap-3 text-sm border-b border-slate-100 pb-2 last:border-0">
                            <span class="w-40 shrink-0 text-slate-400 capitalize">{{ String(key).replace(/_/g, ' ') }}</span>
                            <span class="text-slate-800 font-medium">
                                <template v-if="val && typeof val === 'object' && !Array.isArray(val)">{{ (val as any).name ?? '' }}</template>
                                <template v-else-if="Array.isArray(val)">{{ (val as string[]).join(', ') }}</template>
                                <template v-else>{{ val }}</template>
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Lampiran -->
            <div v-if="lampiran.length > 0" class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">Lampiran</h2>
                <div class="space-y-2">
                    <a v-for="l in lampiran" :key="l.id" :href="l.url" target="_blank"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
                        <FileText class="size-4 text-slate-400 shrink-0" />
                        <span class="text-sm text-slate-700">{{ l.name }}</span>
                    </a>
                </div>
            </div>

            <!-- Aksi Dokumen -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="mb-3 text-sm font-semibold text-slate-800">Dokumen</h2>

                <!-- [BARU] QR Code info -->
                <div class="mb-4 flex items-start gap-3 rounded-xl border px-4 py-3"
                    :class="isFinished ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'">
                    <QrCode class="mt-0.5 size-4 shrink-0"
                        :class="isFinished ? 'text-emerald-600' : 'text-amber-600'" />
                    <div>
                        <p class="text-xs font-semibold"
                            :class="isFinished ? 'text-emerald-800' : 'text-amber-800'">
                            {{ isFinished ? 'QR Code Aktif' : 'QR Code Belum Aktif' }}
                        </p>
                        <p class="text-xs"
                            :class="isFinished ? 'text-emerald-700' : 'text-amber-700'">
                            {{ isFinished
                                ? 'Surat sudah divalidasi. QR Code dapat dipindai untuk verifikasi.'
                                : 'QR Code akan aktif setelah surat selesai divalidasi dan disetujui.'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Tombol validasi admin (hanya jika status pending) -->
                <div v-if="can_approve" class="mb-4 flex flex-wrap gap-3">
                    <button type="button"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"
                        @click="approveAdmin">
                        <ShieldCheck class="size-4" /> Validasi & Teruskan
                    </button>
                    <button type="button"
                        class="flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors"
                        @click="openRejectModal">
                        <XCircle class="size-4" /> Tolak Pengajuan
                    </button>
                </div>

                <!-- Tombol edit (hanya jika ditolak & pernah divalidasi admin) -->
                <div v-if="can_edit" class="mb-4 flex flex-wrap gap-3">
                    <Link :href="`/admin/surat/${id}/edit`"
                        class="flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition-colors">
                        <FileEdit class="size-4" /> Edit & Teruskan
                    </Link>
                </div>

                <div class="flex flex-wrap gap-3">
                    <!-- Preview template -->
                    <button type="button"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                        @click="openPreviewTemplate">
                        <Eye class="size-4 text-slate-400" /> Preview Template
                    </button>

                    <!-- Preview dokumen jadi -->
                    <button v-if="generatedDocumentUrl" type="button"
                        class="flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition-colors"
                        @click="openPreviewDocument">
                        <Eye class="size-4" /> Preview Dokumen
                    </button>

                    <!-- Download PDF -->
                    <button v-if="isFinished" type="button"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"
                        @click="openDownloadPdf">
                        <Download class="size-4" /> Download PDF
                    </button>

                    <!-- Belum selesai -->
                    <div v-if="!isFinished"
                        class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-700">
                        <Clock class="size-4" />
                        Dokumen belum tersedia — surat belum selesai diproses
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tolak Pengajuan -->
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
                            <p class="text-xs text-slate-400">Berikan alasan penolakan untuk pemohon.</p>
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
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
