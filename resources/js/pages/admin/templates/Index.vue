<script setup lang="ts">
// resources/js/pages/admin/templates/Index.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
// import { computed, ref, watch } from 'vue';
import { Plus, Eye, Save, Copy, ToggleLeft, ToggleRight, Trash2, X, Search, Settings, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { ref, reactive, computed, watch } from 'vue';

// const layout = reactive({
//     margin_top:        '',
//     margin_right:      '',
//     margin_bottom:     '',
//     margin_left:       '',
//     body_indent:       0,
//     paragraph_indent:  0,
//     table_indent:      0,
// });

// ── Types ──────────────────────────────────────────────────────────────────
type SuratKomponen =
    | { type: 'judul'; teks: string; align: 'left'|'center'|'right'; font_size?: string; margin_left?: number; bold?: boolean; underline?: boolean }
    | { type: 'subjudul'; teks: string; font_size?: string; margin_left?: number }
    | { type: 'paragraf'; teks: string; align: 'left'|'justify'; italic?: boolean; bold?: boolean; text_indent?: number; font_size?: string; margin_left?: number }
    | { type: 'paragraf_indent'; teks: string; align: 'left'|'justify'; indent?: number; italic?: boolean; bold?: boolean; text_indent?: number; font_size?: string; margin_left?: number }
    | { type: 'header_surat'; nomor: string; lampiran: string; perihal: string; kota: string; tanggal: string; font_size?: string; margin_left?: number }
    | { type: 'kepada_yth'; penerima: string[]; lokasi: string; tempat: string; font_size?: string; margin_left?: number }
    | { type: 'tabel_data'; rows: Array<{ label: string; nilai: string }>; font_size?: string; margin_left?: number }
    | { type: 'tabel_indent'; rows: Array<{ label: string; nilai: string }>; indent?: number; font_size?: string; margin_left?: number }
    | { type: 'tanda_tangan'; kolom: Array<{ jabatan: string; nama: string; nik: string }>; posisi?: 'kiri'|'kanan'|'tengah'|'full'; font_size?: string; margin_left?: number }
    | { type: 'tembusan'; items: string[]; font_size?: string; margin_left?: number }
    | { type: 'spasi'; tinggi: number }
    | { type: 'garis' };

type FieldOption  = { label: string; value: string };
type FieldConfig  = { name: string; label: string; type: string; required: boolean; placeholder: string; help: string; options: FieldOption[] };
type Template     = { id: number; name: string; template_header?: string|null; template_body: string; template_footer?: string|null; version: number; preview_url: string; placeholders: any[] };
type JenisSuratItem = { id: number; nama: string; is_active: boolean; category?: any; template?: { id: number; name: string; version: number }|null };
type JenisSurat   = { id: number; nama: string; slug?: string|null; kode_surat?: string|null; deskripsi?: string|null; is_active: boolean; perlu_approval: boolean; category?: any; allowed_role?: any; approval_role?: any; field_config: FieldConfig[]; template?: Template|null };
type CategoryOption = { id: number; nama: string };
type RoleOption   = { id: number; nama: string; slug: string };
type GlobalSetting= { key: string; label: string; value?: string|null; tipe: string };

const props = defineProps<{
    jenisSurats: JenisSuratItem[];
    selectedJenisSurat: JenisSurat|null;
    selectedJenisSuratId?: number|null;
    categories: CategoryOption[];
    roles: RoleOption[];
    globalSettings: GlobalSetting[];
}>();

const layout = reactive(
    parseCssLayout((props.selectedJenisSurat?.template as any)?.css_style)
);

// ── State ──────────────────────────────────────────────────────────────────
const sidebarSearch      = ref('');
const showAddDialog      = ref(false);
const showGlobalSettings = ref(false);
const activeTab          = ref<'template'|'fields'|'meta'>('template');

const filteredJenisSurats = computed(() => {
    if (!sidebarSearch.value.trim()) return props.jenisSurats;
    const q = sidebarSearch.value.toLowerCase();
    return props.jenisSurats.filter(j =>
        j.nama.toLowerCase().includes(q) || (j.category?.nama ?? '').toLowerCase().includes(q)
    );
});

// ── Form builder ───────────────────────────────────────────────────────────
const isJson = (s?: string|null): boolean => {
    if (!s) return false;
    const t = s.trim();
    if (!t.startsWith('[')) return false;
    try { const d = JSON.parse(t); return Array.isArray(d) && d.length > 0 && typeof d[0]?.type === 'string'; }
    catch { return false; }
};

const parseKomponen = (body?: string|null): SuratKomponen[] => {
    if (!body) return [];
    if (isJson(body)) return JSON.parse(body) as SuratKomponen[];
    // HTML lama: strip tag → 1 paragraf
    const stripped = body.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    return stripped ? [{ type: 'paragraf', teks: stripped, align: 'justify' }] : [];
};

// ── Komponen helpers ───────────────────────────────────────────────────────
const tipeLabel: Record<string, string> = {
    judul: '📝 Judul', subjudul: '📌 Sub Judul',
    paragraf: '📄 Paragraf', paragraf_indent: '↳ Paragraf Indent',
    header_surat: '📋 Header Surat (No/Lamp/Perihal)',
    kepada_yth: '👤 Kepada Yth.',
    tabel_data: '📊 Tabel Data', tabel_indent: '↳ Tabel Indent',
    tanda_tangan: '✍️ Tanda Tangan',
    tembusan: '📋 Tembusan', spasi: '↕ Spasi', garis: '─ Garis',
};

const tipeBorder: Record<string, string> = {
    judul: 'border-blue-300 bg-blue-50', subjudul: 'border-indigo-200 bg-indigo-50',
    paragraf: 'border-slate-200 bg-white', paragraf_indent: 'border-slate-200 bg-slate-50',
    header_surat: 'border-purple-200 bg-purple-50', kepada_yth: 'border-cyan-200 bg-cyan-50',
    tabel_data: 'border-emerald-200 bg-emerald-50', tabel_indent: 'border-emerald-100 bg-emerald-50/50',
    tanda_tangan: 'border-amber-200 bg-amber-50', tembusan: 'border-slate-200 bg-slate-50',
    spasi: 'border-dashed border-slate-300 bg-slate-50', garis: 'border-slate-200 bg-slate-50',
};

const tipeGroups = [
    { label: 'Struktur Surat', items: ['header_surat', 'kepada_yth'] },
    { label: 'Teks', items: ['judul', 'subjudul', 'paragraf'] }, //'paragraf_indent'
    { label: 'Tabel', items: ['tabel_data'] }, //'tabel_indent'
    { label: 'Lainnya', items: ['tanda_tangan', 'tembusan', 'spasi', 'garis'] },
];

function makeDefault(tipe: SuratKomponen['type']): SuratKomponen {
    const d: Record<string, SuratKomponen> = {
        judul:           { type: 'judul', teks: 'JUDUL SURAT', align: 'center', font_size: '12pt', bold: true, underline: false },
        subjudul:        { type: 'subjudul', teks: 'Nomor: {{nomor_surat}}' },
        paragraf:        { type: 'paragraf', teks: '', align: 'justify', text_indent: 0 },
        paragraf_indent: { type: 'paragraf_indent', teks: '', align: 'justify', indent: 40, text_indent: 0 },
        header_surat:    { type: 'header_surat', nomor: '{{nomor_surat}}', lampiran: '-', perihal: '', kota: '{{kota_surat}}', tanggal: '{{tanggal_surat_panjang}}' },
        kepada_yth:      { type: 'kepada_yth', penerima: [''], lokasi: 'di-', tempat: 'Tempat' },
        tabel_data:      { type: 'tabel_data', rows: [{ label: 'Nama', nilai: '{{nama_pemohon}}' }, { label: 'NIM', nilai: '{{nim}}' }] },
        tabel_indent:    { type: 'tabel_indent', rows: [{ label: 'Hari/Tanggal', nilai: '' }, { label: 'Waktu', nilai: '' }, { label: 'Tempat', nilai: '' }], indent: 40 },
        tanda_tangan:    { type: 'tanda_tangan', kolom: [{ jabatan: 'Ketua,', nama: '', nik: '' }], posisi: 'kanan' },
        tembusan:        { type: 'tembusan', items: ['Ka. Prodi', 'Bagian Akademik'] },
        spasi:           { type: 'spasi', tinggi: 12 },
        garis:           { type: 'garis' },
    };
    return d[tipe] as SuratKomponen;
}

function addKomponen(tipe: SuratKomponen['type']) { komponen.value.push(makeDefault(tipe)); }
function removeKomponen(i: number) { komponen.value.splice(i, 1); }
function moveUp(i: number)   { if (i > 0) [komponen.value[i-1], komponen.value[i]] = [komponen.value[i], komponen.value[i-1]]; }
function moveDown(i: number) { if (i < komponen.value.length-1) [komponen.value[i], komponen.value[i+1]] = [komponen.value[i+1], komponen.value[i]]; }

function addRow(k: any)          { k.rows.push({ label: '', nilai: '' }); }
function removeRow(k: any, i: number) { k.rows.splice(i, 1); }
function addKolom(k: any)        { k.kolom.push({ jabatan: '', nama: '', nik: '' }); }
function removeKolom(k: any, i: number) { k.kolom.splice(i, 1); }
function addPenerima(k: any)     { k.penerima.push(''); }
function removePenerima(k: any, i: number) { k.penerima.splice(i, 1); }
function addTembusan(k: any)     { k.items.push(''); }
function removeTembusan(k: any, i: number) { k.items.splice(i, 1); }

const placeholderUmum = [
    { key: 'nomor_surat', label: 'No. Surat' }, { key: 'nama_pemohon', label: 'Nama' },
    { key: 'nim', label: 'NIM' }, { key: 'program_studi', label: 'Prodi' },
    { key: 'tanggal_surat_panjang', label: 'Tanggal' }, { key: 'kota_surat', label: 'Kota' },
    { key: 'semester', label: 'Semester' },
];

function insertPH(target: any, key: string, field: string = 'teks') {
    if (typeof target[field] === 'string') target[field] += `{{${key}}}`;
}

// ── Form ───────────────────────────────────────────────────────────────────
// Parse layout dari css_style template
function parseCssLayout(cssStyle?: string|null) {
    if (!cssStyle) return { margin_top: '', margin_right: '', margin_bottom: '', margin_left: '', body_indent: '', paragraph_indent: '', table_indent: '' };
    const mt = cssStyle.match(/margin:\s*([\d.]+\w+)\s+([\d.]+\w+)\s+([\d.]+\w+)\s+([\d.]+\w+)/);
    const bi = cssStyle.match(/\.surat-content\s*\{[^}]*padding-left:\s*([\d]+)px/);
    const pi = cssStyle.match(/\.surat-paragraf\s*\{[^}]*text-indent:\s*([\d]+)px/);
    const ti = cssStyle.match(/\.surat-tabel\s*\{[^}]*margin-left:\s*([\d]+)px/);
    return {
        margin_top:        mt?.[1] ?? '', margin_right:  mt?.[2] ?? '',
        margin_bottom:     mt?.[3] ?? '', margin_left:   mt?.[4] ?? '',
        body_indent:       bi?.[1] ?? '', paragraph_indent: pi?.[1] ?? '',
        table_indent:      ti?.[1] ?? '',
    };
}

const form = useForm({
    name:             props.selectedJenisSurat?.template?.name ?? '',
    kode_surat:       props.selectedJenisSurat?.kode_surat ?? '',
    category_id:      props.selectedJenisSurat?.category?.id ?? '' as any,
    approval_role_id: props.selectedJenisSurat?.approval_role?.id ?? '' as any,
    allowed_role_id:  props.selectedJenisSurat?.allowed_role?.id ?? '' as any,
    perlu_approval:   props.selectedJenisSurat?.perlu_approval ?? true,
    is_active:        props.selectedJenisSurat?.is_active ?? true,
    field_config:     [...(props.selectedJenisSurat?.field_config ?? [])] as FieldConfig[],
    layout: {
        margin_top:        '',
        margin_right:      '',
        margin_bottom:     '',
        margin_left:       '',
        body_indent:       0,
        paragraph_indent:  0,
        table_indent:      0,
    },
});

const komponen = ref<SuratKomponen[]>(parseKomponen(props.selectedJenisSurat?.template?.template_body));

watch(() => props.selectedJenisSurat, (sel) => {
    komponen.value = parseKomponen(sel?.template?.template_body);
    activeTab.value = 'template';
    form.name             = sel?.template?.name ?? '';
    form.kode_surat       = sel?.kode_surat ?? '';
    form.category_id      = sel?.category?.id ?? '';
    form.approval_role_id = sel?.approval_role?.id ?? '';
    form.allowed_role_id  = sel?.allowed_role?.id ?? '';
    form.perlu_approval   = sel?.perlu_approval ?? true;
    form.is_active        = sel?.is_active ?? true;
    form.field_config     = [...(sel?.field_config ?? [])];
    Object.assign(layout, parseCssLayout((sel?.template as any)?.css_style));
}); //{ immediate: true });

const fieldTypeOptions = [
    { value: 'text', label: 'Teks Singkat' }, { value: 'textarea', label: 'Teks Panjang' },
    { value: 'number', label: 'Angka' }, { value: 'date', label: 'Tanggal' },
    { value: 'select', label: 'Pilihan Dropdown' }, { value: 'checkbox', label: 'Centang' },
    { value: 'repeatable', label: 'Daftar' }, { value: 'recipient', label: 'Kepada Yth.' },
];

function addField() { form.field_config.push({ name: '', label: '', type: 'text', required: false, placeholder: '', help: '', options: [] }); }
function removeField(i: number) { form.field_config.splice(i, 1); }
function syncName(field: FieldConfig) {
    if (!field.name) field.name = field.label.toLowerCase().replace(/[^a-z0-9\s_]/g, '').replace(/\s+/g, '_');
}

function saveTemplate() {
    if (!props.selectedJenisSurat) return;
    console.log('layout:', JSON.stringify(layout)); //todo
    router.put(`/admin/templates/${props.selectedJenisSurat.id}`, {
        name: form.name || props.selectedJenisSurat.nama,
        template_body: JSON.stringify(komponen.value),
        field_config: form.field_config,
        kode_surat: form.kode_surat,
        category_id: form.category_id,
        approval_role_id: form.approval_role_id,
        allowed_role_id: form.allowed_role_id,
        perlu_approval: form.perlu_approval,
        is_active: form.is_active,
        // layout: form.layout,
        layout: layout,
    }, { preserveScroll: true });
}

function deleteTemplate() {
    if (!props.selectedJenisSurat) return;
    if (!confirm(`Hapus jenis surat "${props.selectedJenisSurat.nama}"?`)) return;
    router.delete(`/admin/templates/${props.selectedJenisSurat.id}`, {
        onSuccess: () => router.visit('/admin/templates'),
    });
}

// ── Tambah ─────────────────────────────────────────────────────────────────
const addForm = useForm({
    nama: '', kode_surat: '', category_id: '' as any,
    deskripsi: '', allowed_role_id: '' as any, approval_role_id: '' as any,
    perlu_approval: false, is_active: true,
});

function submitAdd() {
    addForm.post('/admin/templates', {
        onSuccess: () => { showAddDialog.value = false; addForm.reset(); },
    });
}

// ── Global settings ────────────────────────────────────────────────────────
const settingsData = ref<Record<string, string>>(
    Object.fromEntries((props.globalSettings ?? []).map(s => [s.key, s.value ?? '']))
);
function saveGlobalSettings() {
    router.post('/admin/settings/template', { settings: settingsData.value }, {
        preserveScroll: true,
        onSuccess: () => { showGlobalSettings.value = false; },
    });
}

function toggleActive(id: number, nama: string, current: boolean) {
    if (confirm(`${current ? 'Nonaktifkan' : 'Aktifkan'} "${nama}"?`)) {
        router.patch(`/admin/templates/${id}/toggle-active`, {}, { preserveScroll: true });
    }
}

function duplicate(id: number) {
    if (confirm('Duplikat jenis surat ini beserta semua isinya?')) {
        router.post(`/admin/templates/${id}/duplicate`);
    }
}

// Keys per kelompok di dialog pengaturan
const kopKeys     = ['nama_instansi', 'nama_fakultas', 'singkatan', 'keputusan', 'logo_path'];
const footerKeys  = ['nama_instansi_footer', 'alamat_footer', 'telepon', 'website', 'email', 'fax'];
const tampilanKeys= ['warna_primer'];
const nomorKeys   = ['format_nomor', 'kota_surat'];
const marginKeys  = ['margin_top', 'margin_right', 'margin_bottom', 'margin_left'];
const fontKeys    = ['font_size_default'];

function settingLabel(key: string): string {
    const m: Record<string,string> = {
        nama_instansi: 'Nama Instansi (Kop)', nama_fakultas: 'Nama Fakultas', singkatan: 'Singkatan',
        keputusan: 'Teks Keputusan', logo_path: 'Logo (path relatif)',
        nama_instansi_footer: 'Nama Instansi (Footer)', alamat_footer: 'Alamat (Footer)',
        telepon: 'Telepon', website: 'Website', email: 'Email', fax: 'Fax',
        warna_primer: 'Warna Primer', format_nomor: 'Format Nomor Surat', kota_surat: 'Kota Default',
        margin_top: 'Margin Atas', margin_right: 'Margin Kanan',
        margin_bottom: 'Margin Bawah', margin_left: 'Margin Kiri',
        font_size_default: 'Ukuran Font Default',
    };
    return props.globalSettings.find(s => s.key === key)?.label ?? m[key] ?? key;
}
</script>

<template>
    <AdminLayout title="Template Surat" subtitle="Kelola format dan jenis surat" active-menu="templates" :breadcrumbs="[{ label: 'Template Surat' }]">
        <template #actions>
            <button type="button" class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors" @click="showGlobalSettings = true">
                <Settings class="size-3.5 text-slate-500" /> Pengaturan Kop & Footer
            </button>
            <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="showAddDialog = true">
                <Plus class="size-3.5" /> Tambah Jenis Surat
            </button>
        </template>
        <Head title="Template Surat" />

        <div class="flex gap-5">
            <!-- Sidebar -->
            <aside class="w-56 shrink-0 rounded-2xl border border-slate-200 bg-white overflow-hidden self-start sticky top-4">
                <div class="border-b border-slate-100 px-4 py-3 space-y-2">
                    <p class="text-xs font-semibold text-slate-800">Jenis Surat</p>
                    <p class="text-[10px] text-slate-500">{{ filteredJenisSurats.length }} tersedia</p>
                    <div class="relative">
                        <Search class="pointer-events-none absolute left-2.5 top-1/2 size-3 -translate-y-1/2 text-slate-400" />
                        <input v-model="sidebarSearch" type="text" placeholder="Cari..." class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 pl-7 pr-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" />
                    </div>
                </div>
                <div class="max-h-[calc(100vh-220px)] overflow-y-auto divide-y divide-slate-100">
                    <Link v-for="jenis in filteredJenisSurats" :key="jenis.id"
                        :href="`/admin/templates?jenis_surat_id=${jenis.id}`"
                        class="block px-4 py-3 transition-colors hover:bg-slate-50"
                        :class="selectedJenisSuratId === jenis.id ? 'bg-emerald-50 border-l-2 border-emerald-500' : ''">
                        <div class="flex items-start justify-between gap-1">
                            <p class="text-xs font-semibold text-slate-800 leading-tight truncate">{{ jenis.nama }}</p>
                            <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-semibold" :class="jenis.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                {{ jenis.is_active ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5 truncate">{{ jenis.category?.nama ?? 'Tanpa kategori' }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ jenis.template ? `v${jenis.template.version}` : 'Belum ada template' }}</p>
                    </Link>
                </div>
            </aside>

            <!-- Editor -->
            <div class="min-w-0 flex-1">
                <div v-if="!selectedJenisSurat" class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center">
                    <p class="text-sm text-slate-500">Pilih jenis surat dari sidebar kiri.</p>
                    <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="showAddDialog = true">
                        <Plus class="size-4" /> Buat Jenis Surat Baru
                    </button>
                </div>

                <div v-else class="space-y-4">
                    <!-- Header + aksi -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-slate-500">{{ selectedJenisSurat.category?.nama }}</p>
                                <h2 class="text-lg font-bold text-slate-900">{{ selectedJenisSurat.nama }}</h2>
                                <p v-if="selectedJenisSurat.kode_surat" class="font-mono text-xs text-slate-500">{{ selectedJenisSurat.kode_surat }}</p>
                            </div>
                            <div class="flex gap-2 shrink-0 flex-wrap justify-end">
                                <button type="button" class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors" @click="duplicate(selectedJenisSurat.id)">
                                    <Copy class="size-3.5 text-slate-500" /> Duplikat
                                </button>
                                <button type="button"
                                    class="flex items-center gap-1 rounded-xl border px-3 py-1.5 text-xs font-semibold transition-colors"
                                    :class="selectedJenisSurat.is_active ? 'border-red-200 bg-red-50 text-red-600 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    @click="toggleActive(selectedJenisSurat.id, selectedJenisSurat.nama, selectedJenisSurat.is_active)">
                                    <component :is="selectedJenisSurat.is_active ? ToggleRight : ToggleLeft" class="size-3.5" />
                                    {{ selectedJenisSurat.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <button type="button" class="flex items-center gap-1 rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition-colors" @click="deleteTemplate">
                                    <Trash2 class="size-3.5" /> Hapus
                                </button>
                                <button type="button" class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                                    @click="router.visit('/admin/templates')">
                                    <X class="size-3.5" /> Tutup
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-1 border-t border-slate-100 pt-4">
                            <button v-for="tab in (['template','fields','meta'] as const)" :key="tab" type="button"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                :class="activeTab === tab ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                @click="activeTab = tab">
                                {{ tab === 'template' ? '📝 Isi Surat' : tab === 'fields' ? '🔧 Field Dinamis' : '⚙️ Info & Meta' }}
                            </button>
                        </div>
                    </div>

                    <!-- ══ TAB: ISI SURAT ══ -->
                    <div v-if="activeTab === 'template'" class="space-y-4">

                        <!-- Info kop otomatis — tanpa peringatan template lama -->
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800">
                            <p class="font-semibold">✅ Kop & footer otomatis dari Pengaturan Global</p>
                            <p class="mt-0.5">Buat isi surat di bawah. Kop dan footer akan ditambahkan otomatis.</p>
                        </div>

                        <!-- Tombol tambah komponen -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="mb-3 text-xs font-semibold text-slate-700">Tambah Komponen</p>
                            <div class="space-y-2">
                                <div v-for="group in tipeGroups" :key="group.label">
                                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ group.label }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button v-for="tipe in group.items" :key="tipe" type="button"
                                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 transition-colors"
                                            @click="addKomponen(tipe as any)">
                                            {{ tipeLabel[tipe] }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panduan placeholder -->
                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                            <p class="font-semibold mb-1">💡 Placeholder otomatis:</p>
                            <div class="flex flex-wrap gap-1">
                                <code v-for="p in placeholderUmum" :key="p.key" class="rounded bg-blue-100 px-1.5 py-0.5 font-mono text-[10px] text-blue-700">&#123;&#123;{{ p.key }}&#125;&#125;</code>
                            </div>
                        </div>

                        <div v-if="komponen.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white py-12 text-center">
                            <p class="text-sm text-slate-400">Belum ada komponen. Tambahkan dari tombol di atas.</p>
                        </div>

                        <!-- Daftar komponen -->
                        <div v-for="(komp, idx) in komponen" :key="idx"
                            class="rounded-2xl border p-4"
                            :class="tipeBorder[komp.type] ?? 'border-slate-200 bg-white'">

                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-700">{{ tipeLabel[komp.type] }}</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" class="grid size-6 place-items-center rounded-lg bg-white/80 text-slate-400 hover:text-slate-700 disabled:opacity-30" :disabled="idx===0" @click="moveUp(idx)"><ChevronUp class="size-3.5"/></button>
                                    <button type="button" class="grid size-6 place-items-center rounded-lg bg-white/80 text-slate-400 hover:text-slate-700 disabled:opacity-30" :disabled="idx===komponen.length-1" @click="moveDown(idx)"><ChevronDown class="size-3.5"/></button>
                                    <button type="button" class="grid size-6 place-items-center rounded-lg bg-white/80 text-slate-400 hover:text-red-500 hover:bg-red-50" @click="removeKomponen(idx)"><X class="size-3.5"/></button>
                                </div>
                            </div>

                            <!-- Pengaturan font size per komponen -->
                            <div v-if="!['spasi','garis'].includes(komp.type)" class="mb-2 flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">Ukuran font:</span>
                                <select v-model="(komp as any).font_size" class="h-6 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none">
                                    <option value="">Default (12pt)</option>
                                    <option v-for="s in ['8pt','9pt','9.5pt','10pt','10.5pt','11pt','12pt','13pt','14pt']" :key="s" :value="s">{{ s }}</option>
                                </select>
                                <!-- todo -->
                                <!-- <span class="text-[10px] text-slate-400 ml-2">Indent:</span>
                                <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                    class="h-6 w-14 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                <span class="text-[10px] text-slate-400">px</span> -->
                            </div>

                            <!-- HEADER SURAT -->
                            <div v-if="komp.type === 'header_surat'" class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Nomor</span>
                                        <input v-model="(komp as any).nomor" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="{{nomor_surat}}" /></label>
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Lampiran</span>
                                        <input v-model="(komp as any).lampiran" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-emerald-400" placeholder="-" /></label>
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Perihal</span>
                                        <input v-model="(komp as any).perihal" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Judul surat atau {{perihal}}" /></label>
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Kota, Tanggal (kanan)</span>
                                        <input v-model="(komp as any).kota" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="{{kota_surat}}, {{tanggal_surat_panjang}}" /></label>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] text-slate-400 shrink-0">Indent kiri:</span>
                                    <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                        class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                    <span class="text-[10px] text-slate-400">px</span>
                                </div>
                            </div>

                            <!-- KEPADA YTH -->
                            <div v-else-if="komp.type === 'kepada_yth'" class="space-y-2">
                                <div v-for="(p, pi) in (komp as any).penerima" :key="pi" class="flex gap-2">
                                    <input v-model="(komp as any).penerima[pi]" type="text"
                                        class="h-8 flex-1 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400"
                                        placeholder="Bapak/Ibu Nama Jabatan" />
                                    <button type="button" class="text-slate-400 hover:text-red-500" @click="removePenerima(komp, pi)"><X class="size-3.5"/></button>
                                </div>
                                <button type="button" class="flex items-center gap-1 text-xs font-medium text-emerald-700" @click="addPenerima(komp)"><Plus class="size-3.5"/> Tambah Penerima</button>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Lokasi</span>
                                        <input v-model="(komp as any).lokasi" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-emerald-400" /></label>
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Tempat</span>
                                        <input v-model="(komp as any).tempat" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-emerald-400" /></label>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] text-slate-400 shrink-0">Indent kiri:</span>
                                    <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                        class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                    <span class="text-[10px] text-slate-400">px</span>
                                </div>
                            </div>

                            <!-- JUDUL -->
                            <div v-else-if="komp.type === 'judul'" class="space-y-2">
                                <input v-model="(komp as any).teks" type="text"
                                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold uppercase text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="JUDUL SURAT" />
                                <!-- <div class="flex gap-1">
                                    <button v-for="a in [['left','Kiri'],['center','Tengah'],['right','Kanan']]" :key="a[0]" type="button"
                                        class="rounded-lg px-2.5 py-1 text-xs transition-colors"
                                        :class="(komp as any).align===a[0] ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        @click="(komp as any).align=a[0]">{{ a[1] }}</button>
                                </div> -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="flex gap-1">
                                        <button v-for="a in [['left','Kiri'],['center','Tengah'],['right','Kanan']]" :key="a[0]" type="button"
                                            class="rounded-lg px-2.5 py-1 text-xs transition-colors"
                                            :class="(komp as any).align===a[0] ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            @click="(komp as any).align=a[0]">{{ a[1] }}</button>
                                    </div>
                                    <div class="flex gap-1">
                                        <button type="button"
                                            class="rounded-lg px-2.5 py-1 text-xs font-bold transition-colors"
                                            :class="(komp as any).bold ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            @click="(komp as any).bold = !(komp as any).bold">B</button>
                                        <button type="button"
                                            class="rounded-lg px-2.5 py-1 text-xs underline transition-colors"
                                            :class="(komp as any).underline ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            @click="(komp as any).underline = !(komp as any).underline">U</button>
                                    </div>
                                </div>

                            </div>

                            <!-- SUBJUDUL -->
                            <div v-else-if="komp.type === 'subjudul'">
                                <input v-model="(komp as any).teks" type="text"
                                    class="h-9 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400"
                                    placeholder="Sub judul, misal: Nomor: {{nomor_surat}}" />
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] text-slate-400 shrink-0">Indent kiri:</span>
                                    <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                        class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                    <span class="text-[10px] text-slate-400">px</span>
                                </div>
                            </div>

                            <!-- PARAGRAF & PARAGRAF INDENT -->
                            <div v-else-if="komp.type === 'paragraf' || komp.type === 'paragraf_indent'" class="space-y-2">
                                <textarea v-model="(komp as any).teks" rows="3"
                                    class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400"
                                    placeholder="Ketik isi paragraf. Gunakan {{placeholder}} untuk data otomatis." />
                                <div class="flex flex-wrap items-center gap-3">
                                    <!-- Rata teks -->
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-slate-600">Rata:</span>
                                        <button v-for="a in [['left','Kiri'],['justify','Kanan-kiri']]" :key="a[0]" type="button"
                                            class="rounded-lg px-2.5 py-1 text-xs transition-colors"
                                            :class="(komp as any).align===a[0] ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            @click="(komp as any).align=a[0]">{{ a[1] }}</button>
                                    </div>
                                    <!-- Indent blok (paragraf_indent) -->
                                    <!-- <div v-if="komp.type==='paragraf_indent'" class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-600">Indent blok:</span>
                                        <input v-model.number="(komp as any).indent" type="number" min="0" max="120"
                                            class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none" />
                                        <span class="text-xs text-slate-400">px</span>
                                    </div> -->
                                    <!-- Indent baris pertama -->
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-600">Indent baris 1:</span>
                                        <input v-model.number="(komp as any).text_indent" type="number" min="0" max="120"
                                            class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none" />
                                        <span class="text-xs text-slate-400">px</span>
                                    </div>
                                    <!-- Italic + Bold -->
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(komp as any).italic" type="checkbox" class="rounded border-slate-300" />
                                        <span class="text-xs italic text-slate-600">Italic</span>
                                    </label>
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(komp as any).bold" type="checkbox" class="rounded border-slate-300" />
                                        <span class="text-xs font-bold text-slate-600">Bold</span>
                                    </label>
                                    <!-- Indent kiri blok -->
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-600">Indent kiri:</span>
                                        <input v-model.number="(komp as any).margin_left" type="number" min="0" max="200"
                                            class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none" />
                                        <span class="text-xs text-slate-400">px</span>
                                    </div>
                                    <!-- Insert placeholder -->
                                    <div class="flex flex-wrap gap-1">
                                        <button v-for="p in placeholderUmum.slice(0,5)" :key="p.key" type="button"
                                            class="rounded-lg bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 font-mono text-[10px] text-emerald-700 hover:bg-emerald-100"
                                            @click="insertPH(komp, p.key)">+{{ p.label }}</button>
                                    </div>
                                </div>
                            </div>

                            <!-- TABEL DATA / TABEL INDENT -->
                            <div v-else-if="komp.type==='tabel_data' || komp.type==='tabel_indent'" class="space-y-2">
                                <div v-if="komp.type==='tabel_indent'" class="flex items-center gap-2 mb-2">
                                    <span class="text-xs text-slate-600">Indent:</span>
                                    <input v-model.number="(komp as any).indent" type="number" min="0" max="120" class="h-7 w-16 rounded-lg border border-slate-200 bg-white px-2 text-xs text-slate-800 outline-none" />
                                    <span class="text-xs text-slate-400">px</span>
                                </div>
                                <div v-for="(row, ri) in (komp as any).rows" :key="ri" class="flex items-center gap-2">
                                    <input v-model="row.label" type="text" class="h-8 w-32 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Label" />
                                    <span class="font-semibold text-slate-500">:</span>
                                    <input v-model="row.nilai" type="text" class="h-8 flex-1 rounded-lg border border-slate-300 bg-white px-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Nilai atau {{placeholder}}" />
                                    <button type="button" class="text-slate-400 hover:text-red-500" @click="removeRow(komp, ri)"><X class="size-3.5"/></button>
                                </div>
                                <button type="button" class="flex items-center gap-1 text-xs font-medium text-emerald-700" @click="addRow(komp)"><Plus class="size-3.5"/> Tambah Baris</button>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] text-slate-400 shrink-0">Indent kiri:</span>
                                    <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                        class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                    <span class="text-[10px] text-slate-400">px</span>
                                </div>
                            </div>

                            <!-- TANDA TANGAN -->
                            <div v-else-if="komp.type==='tanda_tangan'" class="space-y-3">
                                <!-- Posisi tanda tangan -->
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-600">Posisi:</span>
                                    <div class="flex gap-1">
                                        <button v-for="p in [['kanan','Kanan'],['kiri','Kiri'],['tengah','Tengah'],['full','Penuh']]" :key="p[0]" type="button"
                                            class="rounded-lg px-2.5 py-1 text-xs transition-colors"
                                            :class="(komp as any).posisi===p[0] ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            @click="(komp as any).posisi=p[0]">{{ p[1] }}</button>
                                    </div>
                                </div>
                                <div v-for="(kol, ki) in (komp as any).kolom" :key="ki"
                                    class="rounded-xl border border-amber-200 bg-white p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-slate-700">Kolom {{ ki+1 }}</span>
                                        <button type="button" class="text-slate-400 hover:text-red-500" @click="removeKolom(komp, ki)"><X class="size-3.5"/></button>
                                    </div>
                                    <input v-model="kol.jabatan" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Jabatan (Ketua, Dekan...)" />
                                    <input v-model="kol.nama" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Nama atau {{nama_pemohon}}" />
                                    <input v-model="kol.nik" type="text" class="h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="NIK/NIM" />
                                </div>
                                <button type="button" class="flex items-center gap-1 text-xs font-medium text-emerald-700" @click="addKolom(komp)"><Plus class="size-3.5"/> Tambah Kolom</button>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] text-slate-400 shrink-0">Indent kiri:</span>
                                    <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                        class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                    <span class="text-[10px] text-slate-400">px</span>
                                </div>
                            </div>

                            <!-- TEMBUSAN -->
                            <div v-else-if="komp.type==='tembusan'" class="space-y-2">
                                <div v-for="(item, ti) in (komp as any).items" :key="ti" class="flex items-center gap-2">
                                    <span class="w-5 text-xs text-center text-slate-500">{{ ti+1 }}.</span>
                                    <input v-model="(komp as any).items[ti]" type="text" class="h-8 flex-1 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Nama penerima" />
                                    <button type="button" class="text-slate-400 hover:text-red-500" @click="removeTembusan(komp, ti)"><X class="size-3.5"/></button>
                                </div>
                                <button type="button" class="flex items-center gap-1 text-xs font-medium text-emerald-700" @click="addTembusan(komp)"><Plus class="size-3.5"/> Tambah</button>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="text-[10px] text-slate-400 shrink-0">Indent kiri:</span>
                                    <input v-model.number="(komp as any).margin_left" type="number" min="0" placeholder="0"
                                        class="h-6 w-16 rounded-lg border border-slate-200 bg-white px-1.5 text-[10px] text-slate-700 outline-none" />
                                    <span class="text-[10px] text-slate-400">px</span>
                                </div>
                            </div>

                            <!-- SPASI -->
                            <div v-else-if="komp.type==='spasi'" class="flex items-center gap-3">
                                <span class="text-xs text-slate-600">Tinggi:</span>
                                <input v-model.number="(komp as any).tinggi" type="number" min="4" max="120" class="h-8 w-20 rounded-lg border border-slate-300 bg-white px-2 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                <span class="text-xs text-slate-500">px</span>
                            </div>

                            <!-- GARIS -->
                            <div v-else-if="komp.type==='garis'" class="py-1">
                                <hr class="border-slate-300" />
                            </div>
                        </div>

                        <!-- Layout & Margin per Template -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800">📐 Layout & Margin</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Atur margin halaman dan indent konten khusus untuk template ini</p>
                            </div>

                            <!-- Margin halaman -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 mb-2">Margin Halaman</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Atas</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.margin_top" type="text" placeholder="12mm"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                        </div>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Bawah</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.margin_bottom" type="text" placeholder="25mm"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                        </div>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Kiri</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.margin_left" type="text" placeholder="15mm"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                        </div>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Kanan</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.margin_right" type="text" placeholder="15mm"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                        </div>
                                    </label>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">Format: mm, cm, px — contoh: 15mm, 2cm, 50px</p>
                            </div>

                            <!-- Indent konten -->
                            <div>
                                <p class="text-xs font-semibold text-slate-600 mb-2">Indent Konten</p>
                                <div class="grid grid-cols-3 gap-3">
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Body (kiri+kanan)</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.body_indent" type="number" min="0" placeholder="0"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                            <span class="text-[11px] text-slate-400 shrink-0">px</span>
                                        </div>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Indent Paragraf</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.paragraph_indent" type="number" min="0" placeholder="0"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                            <span class="text-[11px] text-slate-400 shrink-0">px</span>
                                        </div>
                                    </label>
                                    <label class="space-y-1">
                                        <span class="text-[11px] text-slate-500">Indent Tabel</span>
                                        <div class="flex items-center gap-1.5">
                                            <input v-model="layout.table_indent" type="number" min="0" placeholder="0"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-xs text-slate-800 outline-none focus:border-emerald-400" />
                                            <span class="text-[11px] text-slate-400 shrink-0">px</span>
                                        </div>
                                    </label>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">Indent body menambah padding kiri & kanan seluruh konten surat</p>
                            </div>
                        </div>

                        <!-- Simpan -->
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4">
                            <a v-if="selectedJenisSurat.template?.preview_url" :href="selectedJenisSurat.template.preview_url" target="_blank"
                                class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                <Eye class="size-3.5 text-slate-500" /> Preview PDF
                            </a>
                            <div class="ml-auto">
                                <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors" @click="saveTemplate">
                                    <Save class="size-3.5" /> Simpan Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ══ TAB: FIELD DINAMIS ══ -->
                    <div v-if="activeTab==='fields'" class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">Field yang Muncul Saat Buat Surat</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ form.field_config.length }} field</p>
                            </div>
                            <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200" @click="addField"><Plus class="size-3.5"/> Tambah Field</button>
                        </div>
                        <div class="space-y-3">
                            <div v-if="form.field_config.length===0" class="rounded-xl border border-dashed border-slate-200 py-10 text-center text-sm text-slate-400">Belum ada field.</div>
                            <div v-for="(field, idx) in form.field_config" :key="idx" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <code class="rounded-lg bg-emerald-100 px-2 py-0.5 text-[10px] font-mono text-emerald-800">&#123;&#123;{{ field.name || 'nama_field' }}&#125;&#125;</code>
                                    <button type="button" class="text-slate-400 hover:text-red-500" @click="removeField(idx)"><Trash2 class="size-4"/></button>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Label</span>
                                        <input v-model="field.label" type="text" placeholder="Contoh: NIM Mahasiswa" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" @input="syncName(field)" /></label>
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Key (placeholder)</span>
                                        <input v-model="field.name" type="text" placeholder="nim_mahasiswa" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" /></label>
                                    <label class="space-y-1"><span class="text-[10px] font-medium text-slate-600">Tipe Input</span>
                                        <select v-model="field.type" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 outline-none focus:border-emerald-400">
                                            <option v-for="opt in fieldTypeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                        </select></label>
                                    <div class="flex items-end pb-1">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input v-model="field.required" type="checkbox" class="rounded border-slate-300" />
                                            <span class="text-xs text-slate-700">Wajib diisi</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.field_config.length>0" class="mt-4 flex justify-end">
                            <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700" @click="saveTemplate"><Save class="size-3.5"/> Simpan</button>
                        </div>
                    </div>

                    <!-- ══ TAB: META ══ -->
                    <div v-if="activeTab==='meta'" class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h3 class="mb-4 text-sm font-semibold text-slate-800">Informasi Jenis Surat</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Kode Surat</span>
                                <input v-model="form.kode_surat" type="text" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-sm uppercase text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="CUTI-MHS" /></label>
                            <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Kategori</span>
                                <select v-model="form.category_id" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-emerald-400">
                                    <option value="">Tanpa kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nama }}</option>
                                </select></label>
                            <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Role Approver</span>
                                <select v-model="form.approval_role_id" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-emerald-400">
                                    <option value="">Tidak ada (langsung selesai)</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.nama }}</option>
                                </select></label>
                            <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Role yang Boleh Buat</span>
                                <select v-model="form.allowed_role_id" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-emerald-400">
                                    <option value="">Semua role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.nama }}</option>
                                </select></label>
                            <div class="col-span-2 flex items-center gap-6">
                                <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.perlu_approval" type="checkbox" class="rounded border-slate-300 text-emerald-600"/><span class="text-sm text-slate-700">Perlu Approval</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-emerald-600"/><span class="text-sm text-slate-700">Aktif</span></label>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700" @click="saveTemplate"><Save class="size-3.5"/> Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ Dialog: Tambah Jenis Surat ══ -->
        <Dialog :open="showAddDialog" @update:open="v => !v && (showAddDialog=false)">
            <DialogContent class="max-h-[90vh] w-[min(520px,calc(100vw-2rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0 shadow-xl">
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader>
                        <DialogTitle class="text-base font-semibold text-slate-900">Tambah Jenis Surat Baru</DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 mt-1">Template bisa diisi setelah disimpan.</DialogDescription>
                    </DialogHeader>
                </div>
                <form id="add-form" class="px-6 py-5 space-y-4" @submit.prevent="submitAdd">
                    <label class="block space-y-1.5"><span class="text-xs font-medium text-slate-700">Nama Surat <span class="text-red-500">*</span></span>
                        <input v-model="addForm.nama" type="text" required class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Contoh: Surat Undangan Yudisium" />
                        <p v-if="addForm.errors.nama" class="text-xs text-red-500">{{ addForm.errors.nama }}</p></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Kode Surat</span>
                            <input v-model="addForm.kode_surat" type="text" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 font-mono text-sm uppercase text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="UD-YUDIS" /></label>
                        <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Kategori</span>
                            <select v-model="addForm.category_id" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-emerald-400">
                                <option value="">Tanpa kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nama }}</option>
                            </select></label>
                    </div>
                    <label class="space-y-1.5"><span class="text-xs font-medium text-slate-700">Role Approver</span>
                        <select v-model="addForm.approval_role_id" class="h-10 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 outline-none focus:border-emerald-400">
                            <option value="">Tidak ada (langsung selesai)</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.nama }}</option>
                        </select></label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer"><input v-model="addForm.perlu_approval" type="checkbox" class="rounded border-slate-300 text-emerald-600"/><span class="text-sm text-slate-700">Perlu Approval</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input v-model="addForm.is_active" type="checkbox" class="rounded border-slate-300 text-emerald-600"/><span class="text-sm text-slate-700">Aktif</span></label>
                    </div>
                </form>
                <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                    <Button type="button" variant="outline" class="rounded-xl text-slate-700" @click="showAddDialog=false">Batal</Button>
                    <Button type="submit" form="add-form" class="rounded-xl bg-emerald-600 text-white hover:bg-emerald-700" :disabled="addForm.processing">{{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- ══ Dialog: Pengaturan Global — dikelompokkan KOP + FOOTER + TAMPILAN ══ -->
        <Dialog :open="showGlobalSettings" @update:open="v => !v && (showGlobalSettings=false)">
            <DialogContent class="max-h-[90vh] w-[min(620px,calc(100vw-2rem))] overflow-y-auto rounded-2xl border-0 bg-white p-0 shadow-xl">
                <div class="border-b border-slate-100 px-6 py-5">
                    <DialogHeader>
                        <DialogTitle class="text-base font-semibold text-slate-900">Pengaturan Kop & Footer Global</DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 mt-1">Berlaku untuk <strong>semua surat</strong>.</DialogDescription>
                    </DialogHeader>
                </div>
                <div class="px-6 py-5 space-y-4">

                    <!-- KOP / HEADER -->
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 space-y-3">
                        <p class="text-xs font-bold text-blue-800">🏛 Kop / Header Surat</p>
                        <template v-for="key in kopKeys" :key="key">
                            <label v-if="settingsData[key] !== undefined" class="block space-y-1">
                                <span class="text-[10px] font-medium text-slate-700">{{ settingLabel(key) }}</span>
                                <input v-model="settingsData[key]" type="text"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-blue-400" />
                            </label>
                        </template>
                        <p class="text-[10px] text-blue-600">💡 "Nama Instansi" tampil di baris pertama kop (misal: UNUGHA CILACAP). "Singkatan" tampil dalam kurung setelah nama fakultas — kosongkan jika tidak perlu.</p>
                    </div>

                    <!-- FOOTER -->
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 space-y-3">
                        <p class="text-xs font-bold text-emerald-800">📄 Footer Surat</p>
                        <template v-for="key in footerKeys" :key="key">
                            <label v-if="settingsData[key] !== undefined" class="block space-y-1">
                                <span class="text-[10px] font-medium text-slate-700">{{ settingLabel(key) }}</span>
                                <input v-model="settingsData[key]" type="text"
                                    class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" />
                            </label>
                        </template>
                        <p class="text-[10px] text-emerald-600">💡 "Nama Instansi (Footer)" bisa berbeda dari kop — misal nama lengkap universitas di footer. Kosongkan untuk mengikuti Nama Instansi kop.</p>
                    </div>

                    <!-- TAMPILAN -->
                    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 space-y-3">
                        <p class="text-xs font-bold text-purple-800">🎨 Tampilan & Warna</p>
                        <label v-if="settingsData['warna_primer'] !== undefined" class="block space-y-1">
                            <span class="text-[10px] font-medium text-slate-700">Warna Primer Kop & Footer</span>
                            <div class="flex items-center gap-2">
                                <input v-model="settingsData['warna_primer']" type="color" class="h-9 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-0.5" />
                                <input v-model="settingsData['warna_primer']" type="text" class="h-9 w-28 rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 outline-none focus:border-purple-400" placeholder="#00b050" />
                                <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5">
                                    <div class="size-4 rounded border border-slate-200" :style="{backgroundColor: settingsData['warna_primer'] || '#00b050'}" />
                                    <span class="text-xs text-slate-600">Preview</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- MARGIN -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 space-y-3">
                        <p class="text-xs font-bold text-amber-800">📐 Margin Halaman</p>
                        <div class="grid grid-cols-2 gap-3">
                            <template v-for="key in marginKeys" :key="key">
                                <label v-if="settingsData[key] !== undefined" class="space-y-1">
                                    <span class="text-[10px] font-medium text-slate-700">{{ settingLabel(key) }}</span>
                                    <input v-model="settingsData[key]" type="text" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-amber-400" placeholder="12mm" />
                                </label>
                            </template>
                        </div>
                        <p class="text-[10px] text-amber-600">Contoh: 12mm, 15mm, 1cm, 1in</p>
                    </div>

                    <!-- FORMAT NOMOR -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                        <p class="text-xs font-bold text-slate-700">🔢 Format Nomor & Kota</p>
                        <template v-for="key in nomorKeys" :key="key">
                            <label v-if="settingsData[key] !== undefined" class="block space-y-1">
                                <span class="text-[10px] font-medium text-slate-700">{{ settingLabel(key) }}</span>
                                <input v-model="settingsData[key]" type="text" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" />
                            </label>
                        </template>
                        <div class="rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 text-[10px] text-blue-700">
                            Contoh hasil: <strong>CUTI-MHS/0042/IV/2026</strong>
                        </div>
                    </div>

                    <!-- HTML KUSTOM (collapsed) -->
                    <details class="rounded-xl border border-slate-200 bg-slate-50">
                        <summary class="cursor-pointer px-4 py-3 text-xs font-semibold text-slate-700">🛠 HTML Kop & Footer Kustom (lanjutan)</summary>
                        <div class="px-4 pb-4 space-y-3">
                            <p class="text-[10px] text-slate-500">Kosongkan untuk menggunakan kop/footer otomatis dari pengaturan di atas.</p>
                            <label v-if="settingsData['kop_html'] !== undefined" class="block space-y-1">
                                <span class="text-[10px] font-medium text-slate-600">HTML Kop Surat</span>
                                <textarea v-model="settingsData['kop_html']" rows="4" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Kosongkan untuk otomatis" />
                            </label>
                            <label v-if="settingsData['footer_html'] !== undefined" class="block space-y-1">
                                <span class="text-[10px] font-medium text-slate-600">HTML Footer Surat</span>
                                <textarea v-model="settingsData['footer_html']" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-mono text-xs text-slate-800 placeholder-slate-400 outline-none focus:border-emerald-400" placeholder="Kosongkan untuk otomatis" />
                            </label>
                        </div>
                    </details>
                </div>
                <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
                    <Button type="button" variant="outline" class="rounded-xl text-slate-700" @click="showGlobalSettings=false">Batal</Button>
                    <Button type="button" class="rounded-xl bg-emerald-600 text-white hover:bg-emerald-700" @click="saveGlobalSettings">Simpan Semua Pengaturan</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>

