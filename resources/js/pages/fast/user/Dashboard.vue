<script setup lang="ts">
// resources/js/pages/fast/user/Dashboard.vue
import FastLayout from '@/layouts/FastLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, nextTick, ref, watch } from 'vue';

const PdfViewer = defineAsyncComponent(() => import('@/components/PdfViewer.vue'));
import {
    FileText, CheckCircle2, XCircle, Plus,
    Download, Eye, AlertCircle, GraduationCap,
    BookOpen, RotateCcw, X, ZoomIn, ZoomOut, RotateCcw as ResetZoom, ExternalLink,
    MessageSquare,
} from 'lucide-vue-next';

type Summary = { total: number; diproses: number; selesai: number; ditolak: number };
type NoteItem = { catatan: string; oleh?: string | null; created_at?: string | null };
type LatestSubmission = {
    id: number; reference: string; jenisSurat: string; jenisSuratId?: number | null;
    status: string; keperluan: string; hasPdf: boolean;
    rejectionReason?: string | null;
    notes?: NoteItem[];
    submittedAt?: string | null; neededAt?: string | null;
};
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string; label: string; type: string;
    required: boolean; placeholder: string;
    options: FieldOption[];
};
type JenisSuratOption  = {
    id: number; categoryId?: number | null;
    nama: string; slug?: string | null; deskripsi?: string | null;
    fieldConfig?: FieldConfig[];
};
type SuratCategoryOption = { id: number; nama: string; slug: string; deskripsi?: string | null };
type PageProps = { auth: { user: { name?: string } }; flash?: { success?: string } };

const props = defineProps<{
    summary: Summary;
    latest: LatestSubmission[];
    categories: SuratCategoryOption[];
    jenisSurats: JenisSuratOption[];
    userRole: { id?: number | null; name?: string | null; slug?: string | null };
    userProfile: { name?: string | null; identifierLabel?: string | null; identifierValue?: string | null };
    endpoints: { submission: string; jenisSuratBase: string };
}>();

const page             = usePage<PageProps>();
const showFormModal    = ref(false);
const selectedJenis    = ref<JenisSuratOption | null>(null);
const formStep         = ref<'form' | 'preview'>('form');
const toastMessage     = ref('');
const expandedReasonId = ref<number | null>(null);
const expandedNotesId  = ref<number | null>(null);
let toastTimeoutId: number | null = null;

type FieldValue = string | boolean | string[] | number | null;

const submitForm = useForm<{
    jenis_surat_id: string;
    keperluan: string;
    tanggal_kebutuhan: string;
    field_data: Record<string, FieldValue>;
}>({
    jenis_surat_id: '',
    keperluan: '',
    tanggal_kebutuhan: '',
    field_data: {},
});

function initFormData(jenis: JenisSuratOption) {
    submitForm.jenis_surat_id = String(jenis.id);
    submitForm.keperluan = '';
    submitForm.tanggal_kebutuhan = '';
    const fieldValues: Record<string, FieldValue> = {};
    for (const f of jenis.fieldConfig ?? []) {
        if (f.type === 'checkbox') fieldValues[f.name] = false;
        else if (['checkbox-group', 'multiselect'].includes(f.type)) fieldValues[f.name] = [];
        else fieldValues[f.name] = '';
    }
    submitForm.field_data = fieldValues;
}

function fieldDisplayValue(field: FieldConfig): string {
    const val = submitForm.field_data[field.name];
    if (val === null || val === undefined || val === '') return '-';
    if (Array.isArray(val)) {
        if (val.length === 0) return '-';
        const labels = val.map(v => field.options.find(o => o.value === v)?.label ?? String(v));
        return labels.join(', ');
    }
    if (field.type === 'checkbox') return val ? 'Ya' : 'Tidak';
    if (field.options.length > 0) return field.options.find(o => o.value === val)?.label ?? String(val);
    return String(val);
}

function goToPreview() { formStep.value = 'preview'; }
function backToForm()  { formStep.value = 'form'; }

function doSubmit() {
    submitForm.post('/fast/user/submissions', {
        onSuccess: () => closeForm(),
    });
}

// ── Viewer state ─────────────────────────────────────────────────────────────
const viewerOpen  = ref(false);
const viewerUrl   = ref<string | null>(null);
const viewerTitle = ref('');
const viewerMode  = ref<'html' | 'pdf'>('html');
const viewerNomor = ref<string | null>(null);
const iframeZoom  = ref(100);
const iframeLoad  = ref(true);
const iframeError = ref(false);

function openViewer(item: LatestSubmission, mode: 'preview' | 'pdf') {
    iframeZoom.value  = 100;
    iframeLoad.value  = true;
    iframeError.value = false;

    if (mode === 'pdf' && item.hasPdf) {
        viewerUrl.value  = `/admin/surat/${item.id}/pdf`;
        viewerMode.value = 'pdf';
    } else {
        viewerUrl.value  = `/admin/surat/${item.id}/template-preview`;
        viewerMode.value = 'html';
    }
    viewerTitle.value = `${item.jenisSurat} — ${item.reference}`;
    viewerNomor.value = item.reference;
    viewerOpen.value  = true;
}

function closeViewer() {
    viewerOpen.value = false;
    setTimeout(() => { viewerUrl.value = null; }, 200);
}

function openInNewTab() {
    if (viewerUrl.value) window.open(viewerUrl.value, '_blank');
}
// ── End Viewer ────────────────────────────────────────────────────────────────

const firstName = computed(() =>
    String(page.props.auth?.user?.name ?? 'Pengguna').split(' ')[0]
);

function formatDate(date?: string | null) {
    if (!date) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(date));
}

// Timeline 2 tahap saja
const STATUS_STEPS = [
    { key: 'pending', short: 'Diajukan' },
    { key: 'done',    short: 'Divalidasi' },
];

const DONE_STATUSES = ['validated_admin', 'approved_kaprodi', 'approved_dekan', 'finished'];

function getStepIndex(status: string): number {
    if (status === 'rejected') return -1;
    if (DONE_STATUSES.includes(status)) return 1;
    return 0; // pending
}

function statusLabel(status: string) {
    const map: Record<string, string> = {
        pending:           'Menunggu Validasi',
        validated_admin:   'Sudah Divalidasi',
        approved_kaprodi:  'Sudah Divalidasi',
        approved_dekan:    'Sudah Divalidasi',
        finished:          'Sudah Divalidasi',
        rejected:          'Ditolak',
    };
    return map[status] ?? 'Diproses';
}

function statusBadgeClass(status: string) {
    if (status === 'rejected') return 'bg-red-100 text-red-700';
    if (status === 'pending')  return 'bg-amber-100 text-amber-700';
    return 'bg-emerald-100 text-emerald-700';
}

async function showToast(message: string) {
    if (toastTimeoutId !== null) { window.clearTimeout(toastTimeoutId); toastTimeoutId = null; }
    toastMessage.value = '';
    await nextTick();
    toastMessage.value = message;
    toastTimeoutId = window.setTimeout(() => { toastMessage.value = ''; toastTimeoutId = null; }, 3200);
}

watch(() => page.props.flash?.success, (message) => {
    if (typeof message === 'string' && message.length > 0) showToast(message);
}, { immediate: true });

function openForm(jenis: JenisSuratOption) {
    selectedJenis.value = jenis;
    formStep.value = 'form';
    initFormData(jenis);
    showFormModal.value = true;
}

function resubmit(item: LatestSubmission) {
    const jenis = props.jenisSurats.find(j => j.id === item.jenisSuratId);
    if (jenis) {
        openForm(jenis);
    }
}
function closeForm() {
    showFormModal.value = false;
    selectedJenis.value = null;
    submitForm.reset();
    submitForm.clearErrors();
}
function toggleReason(id: number) {
    expandedReasonId.value = expandedReasonId.value === id ? null : id;
}
function toggleNotes(id: number) {
    expandedNotesId.value = expandedNotesId.value === id ? null : id;
}
function todayString() {
    return new Date().toISOString().slice(0, 10);
}
</script>

<template>
    <FastLayout
        title="Dashboard"
        :subtitle="userProfile.identifierLabel + ': ' + (userProfile.identifierValue ?? '-')"
        active-menu="dashboard"
        :breadcrumbs="[{ label: 'Dashboard' }]"
    >
        <Head title="Dashboard — FAST" />

        <!-- Greeting -->
        <div class="mb-6 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-600 to-emerald-700 p-6 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-emerald-100">
                        {{ new Date().getHours() < 12 ? 'Selamat pagi' : new Date().getHours() < 18 ? 'Selamat siang' : 'Selamat malam' }},
                        <span class="font-semibold">{{ firstName }}</span>
                    </p>
                    <h2 class="mt-1 text-2xl font-bold">Portal Surat FMIKOM</h2>
                    <p class="mt-1 text-sm text-emerald-100">Ajukan dan pantau status surat akademik Anda.</p>
                </div>
                <div class="hidden shrink-0 sm:block">
                    <div class="grid size-16 place-items-center rounded-2xl bg-white/20">
                        <GraduationCap class="size-8" />
                    </div>
                </div>
            </div>

            <!-- 4 statistik -->
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div v-for="stat in [
                    { label: 'Total Surat',     value: summary.total,    color: 'bg-white/20' },
                    { label: 'Sedang Diproses', value: summary.diproses, color: 'bg-amber-400/30' },
                    { label: 'Selesai',         value: summary.selesai,  color: 'bg-sky-400/30' },
                    { label: 'Ditolak',         value: summary.ditolak,  color: 'bg-red-500/30' },
                ]" :key="stat.label"
                    class="rounded-xl p-3 text-center" :class="stat.color">
                    <p class="text-2xl font-bold">{{ String(stat.value).padStart(2, '0') }}</p>
                    <p class="text-[11px] text-emerald-100">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
            <!-- Kiri: Riwayat Terbaru -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Pengajuan Terbaru</h3>
                </div>

                <div v-if="latest.length === 0"
                    class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center">
                    <FileText class="mx-auto mb-3 size-10 text-slate-300" />
                    <p class="text-sm font-medium text-slate-500">Belum ada pengajuan surat</p>
                </div>

                <div v-for="item in latest" :key="item.id"
                    class="rounded-2xl border border-slate-200 bg-white p-4 transition-shadow hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ item.jenisSurat }}</p>
                            <p class="truncate text-xs text-slate-500">{{ item.keperluan }}</p>
                            <p class="mt-0.5 font-mono text-[10px] text-slate-400">{{ item.reference }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                                :class="statusBadgeClass(item.status)">
                                {{ statusLabel(item.status) }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ formatDate(item.submittedAt) }}</span>
                        </div>
                    </div>

                    <!-- Timeline 2 tahap -->
                    <div v-if="item.status !== 'rejected'" class="mt-4">
                        <div class="flex items-center">
                            <template v-for="(step, i) in STATUS_STEPS" :key="step.key">
                                <div class="flex flex-col items-center">
                                    <div class="grid size-6 place-items-center rounded-full text-[9px] font-bold transition-colors"
                                        :class="i <= getStepIndex(item.status)
                                            ? 'bg-emerald-600 text-white'
                                            : 'bg-slate-100 text-slate-400'">
                                        <CheckCircle2 v-if="i < getStepIndex(item.status)" class="size-3.5 text-white" />
                                        <span v-else>{{ i + 1 }}</span>
                                    </div>
                                    <p class="mt-1 w-16 text-center text-[9px] leading-tight"
                                        :class="i <= getStepIndex(item.status) ? 'font-semibold text-emerald-600' : 'text-slate-400'">
                                        {{ step.short }}
                                    </p>
                                </div>
                                <div v-if="i < STATUS_STEPS.length - 1"
                                    class="mb-4 h-0.5 flex-1 transition-colors"
                                    :class="i < getStepIndex(item.status) ? 'bg-emerald-400' : 'bg-slate-200'" />
                            </template>
                        </div>
                    </div>

                    <!-- Ditolak -->
                    <div v-if="item.status === 'rejected'" class="mt-3">
                        <div class="flex items-center gap-1.5 rounded-xl border border-red-100 bg-red-50 px-3 py-2">
                            <XCircle class="size-3.5 shrink-0 text-red-500" />
                            <p class="text-xs font-medium text-red-700">Pengajuan ditolak</p>
                        </div>
                        <button v-if="item.rejectionReason" type="button"
                            class="mt-1.5 flex items-center gap-1 text-xs text-red-500 hover:text-red-700"
                            @click="toggleReason(item.id)">
                            <AlertCircle class="size-3.5" />
                            {{ expandedReasonId === item.id ? 'Sembunyikan' : 'Lihat alasan penolakan' }}
                        </button>
                        <div v-if="expandedReasonId === item.id && item.rejectionReason"
                            class="mt-2 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-700">
                            {{ item.rejectionReason }}
                        </div>
                    </div>

                    <!-- Catatan dari approver -->
                    <div v-if="item.notes && item.notes.length > 0" class="mt-3">
                        <button type="button"
                            class="flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800"
                            @click="toggleNotes(item.id)">
                            <MessageSquare class="size-3.5" />
                            {{ expandedNotesId === item.id ? 'Sembunyikan catatan' : `${item.notes.length} catatan dari pemeriksa` }}
                        </button>
                        <div v-if="expandedNotesId === item.id" class="mt-2 space-y-1.5">
                            <div v-for="(note, i) in item.notes" :key="i"
                                class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2">
                                <p class="text-xs text-indigo-800">{{ note.catatan }}</p>
                                <p class="mt-0.5 text-[10px] text-indigo-400">
                                    {{ note.oleh ?? 'Pemeriksa' }}
                                    <span v-if="note.created_at"> · {{ formatDate(note.created_at) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <!-- Download PDF — tersedia setelah divalidasi -->
                        <button v-if="item.hasPdf" type="button"
                            class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                            @click="openViewer(item, 'pdf')">
                            <Download class="size-3.5" /> Download PDF
                        </button>

                        <!-- Preview template -->
                        <button type="button"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                            @click="openViewer(item, 'preview')">
                            <Eye class="size-3.5" /> Preview
                        </button>

                        <!-- Ajukan ulang jika ditolak -->
                        <button v-if="item.status === 'rejected' && item.jenisSuratId && jenisSurats.find(j => j.id === item.jenisSuratId)" type="button"
                            class="flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100"
                            @click="resubmit(item)">
                            <RotateCcw class="size-3.5" /> Ajukan Ulang
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kanan: Ajukan Surat -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-slate-900">Ajukan Surat Baru</h3>

                <div v-for="category in categories" :key="category.id" class="space-y-2">
                    <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <BookOpen class="size-3.5" /> {{ category.nama }}
                    </p>
                    <div v-for="jenis in jenisSurats.filter(j => j.categoryId === category.id)" :key="jenis.id">
                        <button type="button"
                            class="w-full rounded-xl border border-slate-200 bg-white p-3.5 text-left transition hover:border-emerald-300 hover:shadow-sm"
                            @click="openForm(jenis)">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ jenis.nama }}</p>
                                    <p v-if="jenis.deskripsi" class="mt-0.5 line-clamp-1 text-xs text-slate-400">{{ jenis.deskripsi }}</p>
                                </div>
                                <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <Plus class="size-4" />
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Surat tanpa kategori -->
                <div v-if="jenisSurats.filter(j => !j.categoryId).length > 0">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Lainnya</p>
                    <div v-for="jenis in jenisSurats.filter(j => !j.categoryId)" :key="jenis.id">
                        <button type="button"
                            class="w-full rounded-xl border border-slate-200 bg-white p-3.5 text-left transition hover:border-emerald-300 hover:shadow-sm"
                            @click="openForm(jenis)">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ jenis.nama }}</p>
                                    <p v-if="jenis.deskripsi" class="mt-0.5 line-clamp-1 text-xs text-slate-400">{{ jenis.deskripsi }}</p>
                                </div>
                                <div class="grid size-7 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <Plus class="size-4" />
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <div v-if="jenisSurats.length === 0"
                    class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center">
                    <p class="text-sm text-slate-400">Tidak ada jenis surat tersedia</p>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Surat -->
        <Transition name="modal">
            <div v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 sm:items-center"
                @click.self="closeForm">
                <div class="w-full max-w-lg rounded-t-2xl bg-white sm:rounded-2xl">

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">{{ selectedJenis?.nama }}</h3>
                            <p class="text-xs text-slate-400">
                                {{ formStep === 'form' ? 'Isi data pengajuan' : 'Tinjau sebelum mengirim' }}
                            </p>
                        </div>
                        <button type="button"
                            class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100"
                            @click="closeForm">
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Step: Form -->
                    <div v-if="formStep === 'form'" class="max-h-[70vh] overflow-y-auto p-5 space-y-4">

                        <!-- Keperluan -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                Keperluan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="submitForm.keperluan"
                                rows="2"
                                placeholder="Jelaskan keperluan pengajuan surat..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white resize-none"
                            />
                            <p v-if="submitForm.errors.keperluan" class="mt-1 text-xs text-red-500">{{ submitForm.errors.keperluan }}</p>
                        </div>

                        <!-- Tanggal kebutuhan -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                Tanggal Dibutuhkan <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="submitForm.tanggal_kebutuhan"
                                type="date"
                                :min="todayString()"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white"
                            />
                            <p v-if="submitForm.errors.tanggal_kebutuhan" class="mt-1 text-xs text-red-500">{{ submitForm.errors.tanggal_kebutuhan }}</p>
                        </div>

                        <!-- Dynamic fields -->
                        <template v-for="field in selectedJenis?.fieldConfig ?? []" :key="field.name">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </label>

                                <!-- textarea -->
                                <textarea v-if="field.type === 'textarea'"
                                    v-model="(submitForm.field_data as Record<string, FieldValue>)[field.name] as string"
                                    :placeholder="field.placeholder"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 resize-none"
                                />

                                <!-- select -->
                                <select v-else-if="field.type === 'select'"
                                    v-model="(submitForm.field_data as Record<string, FieldValue>)[field.name]"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>

                                <!-- radio -->
                                <div v-else-if="field.type === 'radio'" class="flex flex-wrap gap-3 pt-1">
                                    <label v-for="opt in field.options" :key="opt.value" class="flex items-center gap-1.5 text-sm cursor-pointer">
                                        <input type="radio"
                                            :value="opt.value"
                                            v-model="(submitForm.field_data as Record<string, FieldValue>)[field.name]"
                                            class="text-emerald-600" />
                                        {{ opt.label }}
                                    </label>
                                </div>

                                <!-- checkbox -->
                                <label v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 pt-1 cursor-pointer">
                                    <input type="checkbox"
                                        v-model="(submitForm.field_data as Record<string, FieldValue>)[field.name]"
                                        class="rounded text-emerald-600" />
                                    <span class="text-sm text-slate-700">{{ field.placeholder || field.label }}</span>
                                </label>

                                <!-- checkbox-group / multiselect -->
                                <div v-else-if="['checkbox-group','multiselect'].includes(field.type)" class="flex flex-wrap gap-3 pt-1">
                                    <label v-for="opt in field.options" :key="opt.value" class="flex items-center gap-1.5 text-sm cursor-pointer">
                                        <input type="checkbox"
                                            :value="opt.value"
                                            v-model="(submitForm.field_data as Record<string, FieldValue>)[field.name] as string[]"
                                            class="rounded text-emerald-600" />
                                        {{ opt.label }}
                                    </label>
                                </div>

                                <!-- default: text / number / date / email -->
                                <input v-else
                                    v-model="(submitForm.field_data as Record<string, FieldValue>)[field.name] as string"
                                    :type="field.type === 'number' ? 'number' : field.type === 'date' ? 'date' : field.type === 'email' ? 'email' : 'text'"
                                    :placeholder="field.placeholder"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white"
                                />

                                <p v-if="(submitForm.errors as Record<string,string>)[`field_data.${field.name}`]"
                                    class="mt-1 text-xs text-red-500">
                                    {{ (submitForm.errors as Record<string,string>)[`field_data.${field.name}`] }}
                                </p>
                            </div>
                        </template>
                    </div>

                    <!-- Step: Preview -->
                    <div v-else class="max-h-[70vh] overflow-y-auto p-5 space-y-3">
                        <p class="text-xs text-slate-500">Pastikan data di bawah sudah benar sebelum mengirim.</p>
                        <div class="rounded-xl border border-slate-200 divide-y divide-slate-100">
                            <div class="flex gap-3 px-4 py-2.5">
                                <span class="w-32 shrink-0 text-xs text-slate-400">Jenis Surat</span>
                                <span class="text-xs font-medium text-slate-900">{{ selectedJenis?.nama }}</span>
                            </div>
                            <div class="flex gap-3 px-4 py-2.5">
                                <span class="w-32 shrink-0 text-xs text-slate-400">Keperluan</span>
                                <span class="text-xs text-slate-900">{{ submitForm.keperluan || '-' }}</span>
                            </div>
                            <div class="flex gap-3 px-4 py-2.5">
                                <span class="w-32 shrink-0 text-xs text-slate-400">Tgl Dibutuhkan</span>
                                <span class="text-xs text-slate-900">{{ submitForm.tanggal_kebutuhan || '-' }}</span>
                            </div>
                            <template v-for="field in selectedJenis?.fieldConfig ?? []" :key="field.name">
                                <div class="flex gap-3 px-4 py-2.5">
                                    <span class="w-32 shrink-0 text-xs text-slate-400">{{ field.label }}</span>
                                    <span class="text-xs text-slate-900">{{ fieldDisplayValue(field) }}</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer tombol -->
                    <div class="flex gap-2 border-t border-slate-100 px-5 py-4">
                        <button v-if="formStep === 'preview'" type="button"
                            class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                            @click="backToForm">
                            Kembali Edit
                        </button>
                        <button v-else type="button"
                            class="flex-1 rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                            @click="closeForm">
                            Batal
                        </button>

                        <button v-if="formStep === 'form'" type="button"
                            class="flex-1 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="!submitForm.keperluan || !submitForm.tanggal_kebutuhan"
                            @click="goToPreview">
                            Tinjau Pengajuan →
                        </button>
                        <button v-else type="button"
                            class="flex-1 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="submitForm.processing"
                            @click="doSubmit">
                            {{ submitForm.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Toast -->
        <Transition name="toast">
            <div v-if="toastMessage"
                class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-2.5 text-sm font-medium text-emerald-800 shadow-lg">
                {{ toastMessage }}
            </div>
        </Transition>

        <!-- Document Viewer Modal -->
        <Transition name="fade">
            <div v-if="viewerOpen"
                class="fixed inset-0 z-50 flex flex-col bg-black/70 backdrop-blur-sm"
                @click.self="closeViewer">

                <!-- Mode HTML: header + iframe -->
                <template v-if="viewerMode === 'html'">
                    <div class="flex h-14 shrink-0 items-center justify-between bg-slate-900 px-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-emerald-600">
                                <FileText class="size-4 text-white" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-white">{{ viewerTitle }}</p>
                                <p v-if="viewerNomor" class="font-mono text-[10px] text-slate-400">{{ viewerNomor }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <div class="hidden items-center gap-1 sm:flex">
                                <button type="button"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                                    @click="iframeZoom = Math.max(50, iframeZoom - 10)">
                                    <ZoomOut class="size-4" />
                                </button>
                                <span class="w-12 text-center text-xs text-slate-300">{{ iframeZoom }}%</span>
                                <button type="button"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                                    @click="iframeZoom = Math.min(200, iframeZoom + 10)">
                                    <ZoomIn class="size-4" />
                                </button>
                                <button type="button"
                                    class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                                    @click="iframeZoom = 100">
                                    <ResetZoom class="size-3.5" />
                                </button>
                            </div>
                            <button type="button"
                                class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
                                @click="openInNewTab">
                                <ExternalLink class="size-4" />
                            </button>
                            <button type="button"
                                class="grid size-8 place-items-center rounded-lg text-slate-400 hover:bg-red-600 hover:text-white transition-colors"
                                @click="closeViewer">
                                <X class="size-4" />
                            </button>
                        </div>
                    </div>
                    <div class="relative flex-1 overflow-hidden bg-slate-800">
                        <div v-if="iframeLoad"
                            class="absolute inset-0 z-10 flex items-center justify-center bg-slate-800">
                            <div class="flex flex-col items-center gap-3">
                                <div class="size-10 animate-spin rounded-full border-2 border-slate-600 border-t-emerald-500" />
                                <p class="text-sm text-slate-400">Memuat dokumen...</p>
                            </div>
                        </div>
                        <div class="h-full overflow-auto flex items-start justify-center p-4">
                            <div class="min-h-full w-full max-w-4xl overflow-hidden rounded-lg shadow-2xl"
                                :style="{ transform: `scale(${iframeZoom / 100})`, transformOrigin: 'top center' }">
                                <iframe v-if="viewerUrl" :src="viewerUrl"
                                    class="w-full border-0 bg-white transition-opacity"
                                    style="min-height: 80vh;"
                                    :class="{ 'opacity-0': iframeLoad }"
                                    @load="iframeLoad = false"
                                    @error="iframeLoad = false; iframeError = true" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Mode PDF: PdfViewer -->
                <template v-else-if="viewerMode === 'pdf' && viewerUrl">
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
.modal-enter-active, .modal-leave-active { transition: opacity .2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.toast-enter-active, .toast-leave-active { transition: all .2s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(8px); }
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>