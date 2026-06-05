<script setup lang="ts">
// File: resources/js/pages/admin/templates/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch, nextTick } from 'vue';
import { Plus, Eye, Save, Copy, ToggleLeft, ToggleRight, Trash2, Upload, GripVertical, X, ChevronDown } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

// ── Types ──────────────────────────────────────────────────────────────
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string; label: string; type: string; required: boolean;
    placeholder: string; help: string;
    options: FieldOption[]; repeatable: boolean;
    add_label: string; item_label: string;
};

type Template = {
    id: number; name: string; deskripsi?: string | null;
    source_reference?: string | null; subject?: string | null;
    template_header?: string | null; template_body: string;
    template_footer?: string | null; version: number;
    preview_url: string; docx_path?: string | null;
    placeholders: Array<{ key: string; label: string; source_type: string; source_key?: string | null; is_required: boolean }>;
};

type JenisSurat = {
    id: number; nama: string; slug?: string | null; kode_surat?: string | null;
    is_active: boolean; perlu_approval: boolean;
    category?: { id?: number | null; nama?: string | null } | null;
    allowed_role?: { id?: number | null; nama?: string | null } | null;
    approval_role?: { id?: number | null; nama?: string | null } | null;
    field_config: FieldConfig[];
    template?: Template | null;
};

type JenisSuratListItem = {
    id: number; nama: string; slug?: string | null; is_active: boolean;
    category?: { id?: number | null; nama?: string | null } | null;
    template?: { id: number; name: string; version: number; updated_at?: string | null } | null;
};

type CategoryOption = { id: number; nama: string };
type RoleOption = { id: number; nama: string; slug: string };
type GlobalSetting = { key: string; label: string; value?: string | null; tipe: string };

const props = defineProps<{
    jenisSurats: JenisSuratListItem[];
    selectedJenisSurat: JenisSurat | null;
    selectedJenisSuratId?: number | null;
    categories: CategoryOption[];
    roles: RoleOption[];
    globalSettings: GlobalSetting[];
}>();

// ── State ──────────────────────────────────────────────────────────────
const page = usePage<{ auth: { user?: { name?: string } }; flash?: { success?: string } }>();
const bodyEditor = ref<HTMLTextAreaElement | null>(null);
const insertTarget = ref<'body' | 'header' | 'footer'>('body');
const showAddDialog = ref(false);
const showGlobalSettings = ref(false);
const showFieldBuilder = ref(true);
const activeTab = ref<'template' | 'fields' | 'meta'>('template');

// ── Edit form ──────────────────────────────────────────────────────────
const form = useForm({
    name: props.selectedJenisSurat?.template?.name ?? '',
    deskripsi: props.selectedJenisSurat?.template?.deskripsi ?? '',
    source_reference: props.selectedJenisSurat?.template?.source_reference ?? '',
    subject: props.selectedJenisSurat?.template?.subject ?? '',
    template_header: props.selectedJenisSurat?.template?.template_header ?? '',
    template_body: props.selectedJenisSurat?.template?.template_body ?? '',
    template_footer: props.selectedJenisSurat?.template?.template_footer ?? '',
    kode_surat: props.selectedJenisSurat?.kode_surat ?? '',
    deskripsi_jenis: props.selectedJenisSurat?.deskripsi ?? '',
    category_id: props.selectedJenisSurat?.category?.id ?? '',
    allowed_role_id: props.selectedJenisSurat?.allowed_role?.id ?? '',
    approval_role_id: props.selectedJenisSurat?.approval_role?.id ?? '',
    perlu_approval: props.selectedJenisSurat?.perlu_approval ?? true,
    is_active: props.selectedJenisSurat?.is_active ?? true,
    field_config: [...(props.selectedJenisSurat?.field_config ?? [])] as FieldConfig[],
    docx_file: null as File | null,
});

// ── Add form ───────────────────────────────────────────────────────────
const addForm = useForm({
    nama: '', kode_surat: '', category_id: '', deskripsi: '',
    allowed_role_id: '', approval_role_id: '',
    perlu_approval: true, is_active: true,
    field_config: [] as FieldConfig[],
    template_name: '', template_body: '',
});

// ── Global settings form ───────────────────────────────────────────────
const settingsData = ref<Record<string, string>>(
    Object.fromEntries((props.globalSettings ?? []).map(s => [s.key, s.value ?? '']))
);

watch(() => props.selectedJenisSurat, (sel) => {
    form.defaults({
        name: sel?.template?.name ?? '',
        deskripsi: sel?.template?.deskripsi ?? '',
        source_reference: sel?.template?.source_reference ?? '',
        subject: sel?.template?.subject ?? '',
        template_header: sel?.template?.template_header ?? '',
        template_body: sel?.template?.template_body ?? '',
        template_footer: sel?.template?.template_footer ?? '',
        kode_surat: sel?.kode_surat ?? '',
        deskripsi_jenis: sel?.deskripsi ?? '',
        category_id: sel?.category?.id ?? '',
        allowed_role_id: sel?.allowed_role?.id ?? '',
        approval_role_id: sel?.approval_role?.id ?? '',
        perlu_approval: sel?.perlu_approval ?? true,
        is_active: sel?.is_active ?? true,
        field_config: [...(sel?.field_config ?? [])],
        docx_file: null,
    });
    form.reset();
    form.clearErrors();
}, { immediate: false });

// ── Computed ───────────────────────────────────────────────────────────
const currentAdminName = computed(() => page.props.auth?.user?.name ?? 'Admin');
const selectedTemplate = computed(() => props.selectedJenisSurat?.template ?? null);
const placeholders = computed(() => selectedTemplate.value?.placeholders ?? []);

const fieldTypeOptions = [
    { value: 'text', label: 'Text' },
    { value: 'textarea', label: 'Textarea' },
    { value: 'number', label: 'Number' },
    { value: 'date', label: 'Date' },
    { value: 'select', label: 'Select (pilihan tunggal)' },
    { value: 'radio', label: 'Radio' },
    { value: 'checkbox', label: 'Checkbox (ya/tidak)' },
    { value: 'repeatable', label: 'Repeatable (daftar)' },
    { value: 'recipient', label: 'Kepada Yth. (penerima surat)' },
    { value: 'email', label: 'Email' },
];

// ── Field builder helpers ──────────────────────────────────────────────
function emptyField(): FieldConfig {
    return { name: '', label: '', type: 'text', required: false, placeholder: '', help: '', options: [], repeatable: false, add_label: 'Tambah', item_label: 'Item' };
}

function addField(f: typeof form | typeof addForm) { f.field_config.push(emptyField()); }
function removeField(f: typeof form | typeof addForm, i: number) { f.field_config.splice(i, 1); }
function addOption(field: FieldConfig) { field.options.push({ label: '', value: '' }); }
function removeOption(field: FieldConfig, i: number) { field.options.splice(i, 1); }

function syncName(field: FieldConfig) {
    if (!field.name) {
        field.name = field.label.toLowerCase().replace(/[^a-z0-9\s_]/g, '').replace(/\s+/g, '_').replace(/^_+|_+$/g, '');
    }
}

// ── Insert placeholder ─────────────────────────────────────────────────
function fmtToken(key: string) { return `{{${key}}}`; }

async function insertPlaceholder(key: string) {
    const token = fmtToken(key);
    const ta = bodyEditor.value;
    const field = insertTarget.value === 'header' ? 'template_header' : insertTarget.value === 'footer' ? 'template_footer' : 'template_body';
    const cur = (form as any)[field] ?? '';
    if (!ta) { (form as any)[field] = cur + '\n' + token; return; }
    const start = ta.selectionStart ?? cur.length;
    const end   = ta.selectionEnd   ?? cur.length;
    (form as any)[field] = cur.slice(0, start) + token + cur.slice(end);
    await nextTick();
    ta.focus();
    const c = start + token.length;
    ta.setSelectionRange(c, c);
}

// ── Submit ─────────────────────────────────────────────────────────────
function submit() {
    if (!props.selectedJenisSurat) return;
    form.put(`/admin/templates/${props.selectedJenisSurat.id}`, { preserveScroll: true });
}

function submitAdd() {
    addForm.post('/admin/templates', {
        onSuccess: () => { showAddDialog.value = false; addForm.reset(); },
    });
}

function saveGlobalSettings() {
    router.post('/admin/settings/template', { settings: settingsData.value }, {
        preserveScroll: true,
        onSuccess: () => { showGlobalSettings.value = false; },
    });
}

function duplicate(id: number) {
    if (confirm('Duplikat jenis surat ini?')) router.post(`/admin/templates/${id}/duplicate`);
}

function toggleActive(id: number, nama: string, current: boolean) {
    if (confirm(`${current ? 'Nonaktifkan' : 'Aktifkan'} "${nama}"?`))
        router.patch(`/admin/templates/${id}/toggle-active`, {}, { preserveScroll: true });
}

function formatDate(v?: string | null) {
    if (!v) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(v));
}

// File upload
function onDocxChange(e: Event) {
    const f = (e.target as HTMLInputElement).files?.[0];
    if (f) form.docx_file = f;
}
</script>

<template>
    <AdminLayout
        title="Template Surat"
        subtitle="Kelola format dan jenis surat"
        active-menu="templates"
        :breadcrumbs="[{ label: 'Template Surat' }]"
    >
        <template #actions>
            <button type="button" class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors" @click="showGlobalSettings = true">
                Pengaturan Kop & Footer
            </button>
            <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="showAddDialog = true">
                <Plus class="size-3.5" />
                Tambah Jenis Surat
            </button>
        </template>

        <Head title="Template Surat" />

        <div class="flex gap-5 min-h-0">

            <!-- ── Sidebar kiri: daftar jenis surat ── -->
            <aside class="w-56 shrink-0 rounded-2xl border border-slate-200 bg-white overflow-hidden self-start sticky top-4">
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-700">Jenis Surat</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ jenisSurats.length }} tersedia</p>
                </div>
                <div class="max-h-[calc(100vh-200px)] overflow-y-auto divide-y divide-slate-100">
                    <Link
                        v-for="jenis in jenisSurats"
                        :key="jenis.id"
                        :href="`/admin/templates?jenis_surat_id=${jenis.id}`"
                        class="block px-4 py-3 transition-colors hover:bg-slate-50"
                        :class="selectedJenisSuratId === jenis.id ? 'bg-emerald-50 border-l-2 border-emerald-500' : ''"
                    >
                        <div class="flex items-start justify-between gap-1">
                            <p class="text-xs font-semibold text-slate-900 leading-tight truncate">{{ jenis.nama }}</p>
                            <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-semibold" :class="jenis.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400'">
                                {{ jenis.is_active ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ jenis.category?.nama ?? 'Tanpa kategori' }}</p>
                        <p class="text-[10px] text-slate-300 mt-0.5">
                            {{ jenis.template ? `v${jenis.template.version}` : 'Belum ada template' }}
                        </p>
                    </Link>
                </div>
            </aside>

            <!-- ── Editor utama ── -->
            <div class="min-w-0 flex-1">
                <div v-if="!selectedJenisSurat" class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                    <p class="text-slate-400 text-sm">Pilih jenis surat dari sidebar kiri untuk mulai mengedit.</p>
                    <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors" @click="showAddDialog = true">
                        <Plus class="size-4" /> Atau buat jenis surat baru
                    </button>
                </div>

                <div v-else class="space-y-4">
                    <!-- Header info -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-slate-400">{{ selectedJenisSurat.category?.nama }}</p>
                                <h2 class="text-lg font-bold text-slate-900">{{ selectedJenisSurat.nama }}</h2>
                                <p v-if="selectedJenisSurat.kode_surat" class="font-mono text-xs text-slate-400">{{ selectedJenisSurat.kode_surat }}</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button type="button" class="flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors" @click="duplicate(selectedJenisSurat.id)">
                                    <Copy class="size-3.5" /> Duplikat
                                </button>
                                <button type="button"
                                    class="flex items-center gap-1 rounded-xl border px-3 py-1.5 text-xs font-medium transition-colors"
                                    :class="selectedJenisSurat.is_active ? 'border-red-200 bg-red-50 text-red-600 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                                    @click="toggleActive(selectedJenisSurat.id, selectedJenisSurat.nama, selectedJenisSurat.is_active)"
                                >
                                    <component :is="selectedJenisSurat.is_active ? ToggleRight : ToggleLeft" class="size-3.5" />
                                    {{ selectedJenisSurat.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="mt-4 flex gap-1 border-t border-slate-100 pt-4">
                            <button v-for="tab in (['template', 'fields', 'meta'] as const)" :key="tab" type="button"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                :class="activeTab === tab ? 'bg-emerald-100 text-emerald-700' : 'text-slate-500 hover:bg-slate-50'"
                                @click="activeTab = tab"
                            >
                                {{ tab === 'template' ? 'Isi Template' : tab === 'fields' ? 'Field Dinamis' : 'Info & Meta' }}
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submit">

                        <!-- Tab: Template -->
                        <div v-if="activeTab === 'template'" class="grid gap-4 xl:grid-cols-[1fr_260px]">
                            <div class="space-y-4">

                                <!-- Upload docx -->
                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <h3 class="text-sm font-semibold text-slate-800 mb-3">Upload Template Word (.docx)</h3>
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-slate-200 p-4 transition-colors hover:border-emerald-300 hover:bg-emerald-50">
                                        <Upload class="size-5 text-slate-400" />
                                        <div>
                                            <p class="text-sm font-medium text-slate-700">
                                                {{ form.docx_file ? form.docx_file.name : 'Pilih file .docx' }}
                                            </p>
                                            <p class="text-xs text-slate-400">Upload template Word untuk di-konversi otomatis</p>
                                        </div>
                                        <input type="file" accept=".docx" class="hidden" @change="onDocxChange" />
                                    </label>
                                    <p v-if="selectedTemplate?.docx_path" class="mt-2 text-xs text-slate-400">
                                        File aktif: {{ selectedTemplate.docx_path }}
                                    </p>
                                </div>

                                <!-- Template editor -->
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                                    <label class="block space-y-1.5">
                                        <span class="text-xs font-medium text-slate-700">Nama Template</span>
                                        <input v-model="form.name" type="text" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" placeholder="Nama format surat" />
                                    </label>

                                    <label class="block space-y-1.5">
                                        <span class="text-xs font-medium text-slate-700">Isi Surat (Body) <span class="text-red-500">*</span></span>
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            <button type="button" class="rounded-lg px-2.5 py-1 text-[10px] font-semibold transition-colors" :class="insertTarget === 'body' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" @click="insertTarget = 'body'">Isi Utama</button>
                                            <button type="button" class="rounded-lg px-2.5 py-1 text-[10px] font-semibold transition-colors" :class="insertTarget === 'header' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" @click="insertTarget = 'header'">Bagian Atas</button>
                                            <button type="button" class="rounded-lg px-2.5 py-1 text-[10px] font-semibold transition-colors" :class="insertTarget === 'footer' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" @click="insertTarget = 'footer'">Bagian Bawah</button>
                                        </div>
                                        <textarea
                                            ref="bodyEditor"
                                            v-model="form.template_body"
                                            rows="16"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-xs leading-6 text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                            placeholder="Tulis isi surat di sini. Gunakan {{placeholder}} untuk data otomatis."
                                            @focus="insertTarget = 'body'"
                                        />
                                    </label>

                                    <details class="rounded-xl border border-slate-100">
                                        <summary class="cursor-pointer px-4 py-3 text-xs font-semibold text-slate-700">Pengaturan Lanjutan (header, footer, referensi)</summary>
                                        <div class="space-y-3 border-t border-slate-100 px-4 py-4">
                                            <label class="block space-y-1.5">
                                                <span class="text-xs font-medium text-slate-700">Bagian Atas</span>
                                                <textarea v-model="form.template_header" rows="5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-700 outline-none focus:border-emerald-400" placeholder="Opsional. Kop, logo, dll." @focus="insertTarget = 'header'" />
                                            </label>
                                            <label class="block space-y-1.5">
                                                <span class="text-xs font-medium text-slate-700">Bagian Bawah</span>
                                                <textarea v-model="form.template_footer" rows="5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-700 outline-none focus:border-emerald-400" placeholder="Opsional. Tanda tangan, tembusan, dll." @focus="insertTarget = 'footer'" />
                                            </label>
                                            <label class="block space-y-1.5">
                                                <span class="text-xs font-medium text-slate-700">File Acuan</span>
                                                <input v-model="form.source_reference" type="text" class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" placeholder="Nama file acuan .docx" />
                                            </label>
                                        </div>
                                    </details>

                                    <!-- Submit -->
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                                        <p class="text-xs text-slate-400">Versi aktif: v{{ selectedTemplate?.version ?? 1 }} · {{ formatDate(jenisSurats.find(j => j.id === selectedJenisSuratId)?.template?.updated_at) }}</p>
                                        <div class="flex gap-2">
                                            <a v-if="selectedTemplate?.preview_url" :href="selectedTemplate.preview_url" target="_blank" class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                                <Eye class="size-3.5" /> Preview
                                            </a>
                                            <button type="submit" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" :disabled="form.processing">
                                                <Save class="size-3.5" /> Simpan Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sisipkan placeholder -->
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 self-start sticky top-4">
                                <h3 class="text-sm font-semibold text-slate-800 mb-1">Sisipkan Data Otomatis</h3>
                                <p class="text-xs text-slate-400 mb-3">Klik untuk menyisipkan ke area aktif</p>
                                <div class="space-y-2">
                                    <button
                                        v-for="p in placeholders.slice(0, 10)"
                                        :key="p.key"
                                        type="button"
                                        class="flex w-full items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-left hover:border-emerald-200 hover:bg-emerald-50 transition-colors"
                                        @click="insertPlaceholder(p.key)"
                                    >
                                        <div>
                                            <p class="text-xs font-medium text-slate-800">{{ p.label }}</p>
                                            <p class="font-mono text-[10px] text-slate-400">{{ fmtToken(p.key) }}</p>
                                        </div>
                                        <Plus class="size-3.5 text-emerald-500 shrink-0" />
                                    </button>
                                    <div v-if="placeholders.length === 0" class="rounded-xl border border-dashed border-slate-200 py-4 text-center text-xs text-slate-400">
                                        Belum ada placeholder
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Fields -->
                        <div v-if="activeTab === 'fields'" class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800">Field Dinamis</h3>
                                    <p class="text-xs text-slate-400">{{ form.field_config.length }} field · tampil saat mengisi surat</p>
                                </div>
                                <button type="button" class="flex items-center gap-1 rounded-xl bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200 transition-colors" @click="addField(form)">
                                    <Plus class="size-3.5" /> Tambah Field
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div v-if="form.field_config.length === 0" class="rounded-xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-400">
                                    Belum ada field. Klik "Tambah Field" untuk menambahkan.
                                </div>

                                <div v-for="(field, idx) in form.field_config" :key="idx" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <GripVertical class="size-4 text-slate-300" />
                                            <span class="rounded-lg bg-emerald-100 px-2 py-0.5 font-mono text-[10px] text-emerald-700">{{ fmtToken(field.name || 'nama_field') }}</span>
                                        </div>
                                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors" @click="removeField(form, idx)">
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="space-y-1">
                                            <span class="text-[10px] font-medium text-slate-600">Label</span>
                                            <input v-model="field.label" type="text" placeholder="Contoh: NIM Mahasiswa" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" @input="syncName(field)" />
                                        </label>
                                        <label class="space-y-1">
                                            <span class="text-[10px] font-medium text-slate-600">Key (nama)</span>
                                            <input v-model="field.name" type="text" placeholder="Contoh: nim" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 font-mono text-xs text-slate-700 outline-none focus:border-emerald-400" />
                                        </label>
                                        <label class="space-y-1">
                                            <span class="text-[10px] font-medium text-slate-600">Tipe</span>
                                            <select v-model="field.type" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-700 outline-none focus:border-emerald-400">
                                                <option v-for="opt in fieldTypeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                            </select>
                                        </label>
                                        <label class="space-y-1">
                                            <span class="text-[10px] font-medium text-slate-600">Placeholder</span>
                                            <input v-model="field.placeholder" type="text" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-700 outline-none focus:border-emerald-400" />
                                        </label>

                                        <!-- Options untuk select/radio -->
                                        <div v-if="['select', 'radio'].includes(field.type)" class="col-span-2 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-medium text-slate-600">Pilihan</span>
                                                <button type="button" class="text-[10px] text-emerald-600 hover:underline" @click="addOption(field)">+ Tambah Opsi</button>
                                            </div>
                                            <div v-for="(opt, oi) in field.options" :key="oi" class="flex gap-2">
                                                <input v-model="opt.label" type="text" placeholder="Label" class="h-8 flex-1 rounded-lg border border-slate-200 bg-white px-2 text-xs outline-none focus:border-emerald-400" />
                                                <input v-model="opt.value" type="text" placeholder="Value" class="h-8 flex-1 rounded-lg border border-slate-200 bg-white px-2 font-mono text-xs outline-none focus:border-emerald-400" />
                                                <button type="button" class="text-slate-400 hover:text-red-500" @click="removeOption(field, oi)"><X class="size-4" /></button>
                                            </div>
                                        </div>

                                        <div class="col-span-2 flex items-center gap-3">
                                            <input :id="`req-${idx}`" v-model="field.required" type="checkbox" class="rounded border-slate-300" />
                                            <label :for="`req-${idx}`" class="text-xs text-slate-600">Wajib diisi</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.field_config.length > 0" class="mt-4 flex justify-end">
                                <button type="submit" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" :disabled="form.processing">
                                    <Save class="size-3.5" /> Simpan Perubahan
                                </button>
                            </div>
                        </div>

                        <!-- Tab: Meta -->
                        <div v-if="activeTab === 'meta'" class="rounded-2xl border border-slate-200 bg-white p-5">
                            <h3 class="text-sm font-semibold text-slate-800 mb-4">Informasi Jenis Surat</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Kode Surat</span>
                                    <input v-model="form.kode_surat" type="text" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 font-mono text-sm uppercase text-slate-700 outline-none focus:border-emerald-400" placeholder="Contoh: CUTI-MHS" />
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Kategori</span>
                                    <select v-model="form.category_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400">
                                        <option value="">Tanpa kategori</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nama }}</option>
                                    </select>
                                </label>
                                <label class="col-span-2 space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Deskripsi</span>
                                    <input v-model="form.deskripsi_jenis" type="text" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400" placeholder="Deskripsi singkat jenis surat" />
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Role Approver</span>
                                    <select v-model="form.approval_role_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400">
                                        <option value="">Tidak ada (langsung selesai)</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.nama }}</option>
                                    </select>
                                </label>
                                <label class="space-y-1.5">
                                    <span class="text-xs font-medium text-slate-700">Role yang Boleh Membuat</span>
                                    <select v-model="form.allowed_role_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400">
                                        <option value="">Semua role</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.nama }}</option>
                                    </select>
                                </label>
                                <div class="col-span-2 flex items-center gap-6">
                                    <div class="flex items-center gap-2">
                                        <input id="perlu_approval" v-model="form.perlu_approval" type="checkbox" class="rounded border-slate-300" />
                                        <label for="perlu_approval" class="text-sm text-slate-700">Perlu Approval</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-slate-300" />
                                        <label for="is_active" class="text-sm text-slate-700">Aktif</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" :disabled="form.processing">
                                    <Save class="size-3.5" /> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── Dialog: Tambah Jenis Surat ── -->
        <Dialog :open="showAddDialog" @update:open="v => !v && (showAddDialog = false)">
            <DialogContent class="max-h-[90vh] w-[min(640px,calc(100vw-2rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0">
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-semibold text-slate-900">Tambah Jenis Surat</DialogTitle>
                        <DialogDescription class="text-sm text-slate-400">Buat jenis surat baru beserta konfigurasi field dinamisnya.</DialogDescription>
                    </DialogHeader>
                </div>
                <form id="add-jenis-form" class="px-6 py-5 space-y-4" @submit.prevent="submitAdd">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="col-span-2 space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Nama Surat <span class="text-red-500">*</span></span>
                            <input v-model="addForm.nama" type="text" required class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400" placeholder="Contoh: Surat Undangan PKL" />
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Kode Surat <span class="text-red-500">*</span></span>
                            <input v-model="addForm.kode_surat" type="text" required class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 font-mono text-sm uppercase text-slate-700 outline-none focus:border-emerald-400" placeholder="UNDANGAN-PKL" />
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Kategori</span>
                            <select v-model="addForm.category_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400">
                                <option value="">Tanpa kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nama }}</option>
                            </select>
                        </label>
                        <label class="col-span-2 space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Deskripsi</span>
                            <input v-model="addForm.deskripsi" type="text" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400" />
                        </label>
                        <label class="space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">Role Approver</span>
                            <select v-model="addForm.approval_role_id" class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400">
                                <option value="">Tidak ada</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.nama }}</option>
                            </select>
                        </label>
                        <div class="flex items-center gap-4 pt-6">
                            <label class="flex items-center gap-2">
                                <input v-model="addForm.perlu_approval" type="checkbox" class="rounded" />
                                <span class="text-sm text-slate-700">Perlu Approval</span>
                            </label>
                        </div>
                    </div>

                    <!-- Field config mini builder -->
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-slate-700">Field Dinamis</span>
                            <button type="button" class="flex items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-200 transition-colors" @click="addField(addForm)">
                                <Plus class="size-3" /> Tambah
                            </button>
                        </div>
                        <div class="space-y-2">
                            <div v-for="(field, idx) in addForm.field_config" :key="idx" class="grid grid-cols-2 gap-2 rounded-lg border border-slate-200 bg-white p-3">
                                <input v-model="field.label" type="text" placeholder="Label" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs outline-none focus:border-emerald-400" @input="syncName(field)" />
                                <input v-model="field.name" type="text" placeholder="key_name" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2 font-mono text-xs outline-none focus:border-emerald-400" />
                                <select v-model="field.type" class="h-8 rounded-lg border border-slate-200 bg-slate-50 px-2 text-xs outline-none focus:border-emerald-400">
                                    <option v-for="opt in fieldTypeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <div class="flex items-center justify-between gap-2">
                                    <label class="flex items-center gap-1 text-xs text-slate-600">
                                        <input v-model="field.required" type="checkbox" class="rounded" /> Wajib
                                    </label>
                                    <button type="button" class="text-slate-400 hover:text-red-500" @click="removeField(addForm, idx)">
                                        <X class="size-4" />
                                    </button>
                                </div>
                            </div>
                            <div v-if="addForm.field_config.length === 0" class="py-4 text-center text-xs text-slate-400">Belum ada field</div>
                        </div>
                    </div>
                </form>
                <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                    <Button type="button" variant="outline" class="rounded-xl" @click="showAddDialog = false">Batal</Button>
                    <Button type="submit" form="add-jenis-form" class="rounded-xl bg-emerald-600 text-white hover:bg-emerald-700" :disabled="addForm.processing">
                        Simpan Jenis Surat
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- ── Dialog: Global Settings ── -->
        <Dialog :open="showGlobalSettings" @update:open="v => !v && (showGlobalSettings = false)">
            <DialogContent class="max-h-[90vh] w-[min(600px,calc(100vw-2rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0">
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-semibold text-slate-900">Pengaturan Kop & Footer Global</DialogTitle>
                        <DialogDescription class="text-sm text-slate-400">Pengaturan ini berlaku untuk semua template surat kecuali yang di-override.</DialogDescription>
                    </DialogHeader>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <template v-for="setting in globalSettings" :key="setting.key">
                        <label class="block space-y-1.5">
                            <span class="text-xs font-medium text-slate-700">{{ setting.label }}</span>
                            <textarea
                                v-if="setting.tipe === 'html'"
                                v-model="settingsData[setting.key]"
                                rows="4"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-700 outline-none focus:border-emerald-400"
                                :placeholder="`HTML untuk ${setting.label}`"
                            />
                            <input
                                v-else
                                v-model="settingsData[setting.key]"
                                type="text"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400"
                            />
                        </label>
                    </template>
                </div>
                <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                    <Button type="button" variant="outline" class="rounded-xl" @click="showGlobalSettings = false">Batal</Button>
                    <Button type="button" class="rounded-xl bg-emerald-600 text-white hover:bg-emerald-700" @click="saveGlobalSettings">
                        Simpan Pengaturan
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
