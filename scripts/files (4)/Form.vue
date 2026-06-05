<script setup lang="ts">
// resources/js/pages/admin/letters/Form.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ChevronRight, ChevronLeft, Plus, X, Search } from 'lucide-vue-next';

// ── Types ──────────────────────────────────────────────────────────────────
type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string; label: string; type: string; required: boolean;
    placeholder?: string; help?: string;
    options?: FieldOption[];
    add_label?: string; item_label?: string;
};

type JenisSurat = {
    id: number; nama: string; kode_surat?: string | null;
    deskripsi?: string | null; perlu_approval: boolean;
    qr_mode?: string;
    category?: { nama?: string | null } | null;
    field_config: FieldConfig[];
    template?: { name?: string | null } | null;
};

const props = defineProps<{
    jenisSurat: JenisSurat;
    nomorPreview?: string | null;
    kepada_yth_presets?: string[];
}>();

// ── Preset Kepada Yth ──────────────────────────────────────────────────────
const defaultPresets = [
    'Dekan FMIKOM',
    'Ketua Program Studi Informatika',
    'Ketua Program Studi Sistem Informasi',
    'Kepala Laboratorium',
    'Sekretaris Fakultas',
    'Wakil Dekan Bidang Akademik',
];
const presets = computed(() => props.kepada_yth_presets?.length ? props.kepada_yth_presets : defaultPresets);

// ── Form ───────────────────────────────────────────────────────────────────
const form = useForm({
    jenis_surat_id: props.jenisSurat.id,
    keperluan: '',
    kepada_yth: [] as string[],
    lampiran_keterangan: '',
    data: Object.fromEntries(
        props.jenisSurat.field_config
            .filter(f => f.type !== 'recipient')
            .map(f => [f.name, f.type === 'checkbox' ? false : ''])
    ) as Record<string, string | boolean | string[]>,
});

// ── Kepada Yth state ───────────────────────────────────────────────────────
const kepadaSearch = ref('');
const kepadaManual = ref('');
const showKepadaDropdown = ref(false);

const filteredPresets = computed(() =>
    presets.value.filter(p =>
        p.toLowerCase().includes(kepadaSearch.value.toLowerCase()) &&
        !form.kepada_yth.includes(p)
    )
);

function addKepada(value: string) {
    const v = value.trim();
    if (v && !form.kepada_yth.includes(v)) {
        form.kepada_yth.push(v);
    }
    kepadaSearch.value = '';
    kepadaManual.value = '';
    showKepadaDropdown.value = false;
}

function removeKepada(index: number) {
    form.kepada_yth.splice(index, 1);
}

// ── Repeatable field ───────────────────────────────────────────────────────
function addRepeatItem(fieldName: string, addLabel: string) {
    const cur = form.data[fieldName];
    if (Array.isArray(cur)) cur.push('');
    else form.data[fieldName] = [''];
}

function removeRepeatItem(fieldName: string, index: number) {
    const cur = form.data[fieldName];
    if (Array.isArray(cur)) cur.splice(index, 1);
}

// ── Submit ─────────────────────────────────────────────────────────────────
function submit() {
    form.post('/admin/surat/preview', {
        onSuccess: () => {},
    });
}

// ── Field error helper ─────────────────────────────────────────────────────
function fieldError(name: string): string | undefined {
    return (form.errors as any)[`data.${name}`] ?? (form.errors as any)[name];
}

// ── Required fields check ──────────────────────────────────────────────────
const hasRecipientField = computed(() =>
    props.jenisSurat.field_config.some(f => f.type === 'recipient')
);
</script>

<template>
    <AdminLayout
        title="Isi Data Surat"
        :subtitle="`${jenisSurat.category?.nama ?? 'Surat'} — ${jenisSurat.nama}`"
        active-menu="letters.create"
        :breadcrumbs="[
            { label: 'Buat Surat', href: '/admin/surat/create' },
            { label: 'Isi Form' },
        ]"
    >
        <Head :title="`Form — ${jenisSurat.nama}`" />

        <!-- Step indicator -->
        <div class="mb-6 flex items-center gap-0 rounded-2xl border border-slate-200 bg-white px-6 py-4">
            <div v-for="(step, i) in [
                { n: 1, label: 'Pilih Jenis Surat', done: true },
                { n: 2, label: 'Isi Data Surat', active: true },
                { n: 3, label: 'Preview & Submit' },
            ]" :key="step.n" class="flex items-center gap-0 flex-1">
                <div class="flex items-center gap-2 shrink-0">
                    <div class="grid size-7 place-items-center rounded-full text-xs font-bold"
                        :class="step.done ? 'bg-emerald-600 text-white' : step.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400'">
                        <span v-if="step.done && !step.active">✓</span>
                        <span v-else>{{ step.n }}</span>
                    </div>
                    <span class="hidden text-xs font-medium sm:block"
                        :class="step.active ? 'text-emerald-700' : step.done ? 'text-slate-400' : 'text-slate-400'">
                        {{ step.label }}
                    </span>
                </div>
                <div v-if="i < 2" class="mx-3 flex-1 h-px bg-slate-200" />
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_300px]">

            <!-- Form utama -->
            <form class="space-y-4" @submit.prevent="submit">

                <!-- Info surat -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-start gap-3">
                        <div class="rounded-xl bg-emerald-50 p-2.5">
                            <div class="size-5 rounded-lg bg-emerald-600" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">{{ jenisSurat.category?.nama }}</p>
                            <h2 class="text-base font-bold text-slate-900">{{ jenisSurat.nama }}</h2>
                            <p v-if="jenisSurat.deskripsi" class="mt-1 text-xs text-slate-500">{{ jenisSurat.deskripsi }}</p>
                        </div>
                    </div>
                </div>

                <!-- Keperluan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-4 text-sm font-semibold text-slate-800">Informasi Umum</h3>
                    <label class="block space-y-1.5">
                        <span class="text-xs font-medium text-slate-700">Keperluan Surat <span class="text-red-500">*</span></span>
                        <input
                            v-model="form.keperluan"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            :class="form.errors.keperluan ? 'border-red-300 bg-red-50' : ''"
                            placeholder="Contoh: Untuk keperluan akademik"
                        />
                        <p v-if="form.errors.keperluan" class="text-xs text-red-500">{{ form.errors.keperluan }}</p>
                    </label>

                    <label class="mt-3 block space-y-1.5">
                        <span class="text-xs font-medium text-slate-700">Keterangan Lampiran</span>
                        <input
                            v-model="form.lampiran_keterangan"
                            type="text"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            placeholder="Contoh: 1 (satu) lembar"
                        />
                    </label>
                </div>

                <!-- Kepada Yth (muncul jika ada field type recipient atau selalu tampil) -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-1 text-sm font-semibold text-slate-800">Kepada Yth.</h3>
                    <p class="mb-4 text-xs text-slate-400">Pilih dari daftar atau ketik manual. Bisa lebih dari satu.</p>

                    <!-- Penerima terpilih -->
                    <div v-if="form.kepada_yth.length > 0" class="mb-3 flex flex-wrap gap-2">
                        <div
                            v-for="(k, idx) in form.kepada_yth"
                            :key="idx"
                            class="flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1"
                        >
                            <span class="text-xs font-medium text-emerald-800">{{ k }}</span>
                            <button type="button" class="text-emerald-500 hover:text-emerald-700" @click="removeKepada(idx)">
                                <X class="size-3" />
                            </button>
                        </div>
                    </div>

                    <!-- Input search + dropdown -->
                    <div class="relative">
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <Search class="pointer-events-none absolute left-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="kepadaSearch"
                                    type="text"
                                    placeholder="Cari atau pilih penerima..."
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm outline-none focus:border-emerald-400"
                                    @focus="showKepadaDropdown = true"
                                    @blur="setTimeout(() => showKepadaDropdown = false, 200)"
                                    @keyup.enter.prevent="kepadaSearch && addKepada(kepadaSearch)"
                                />
                            </div>
                            <!-- Input manual -->
                            <div class="flex gap-2">
                                <input
                                    v-model="kepadaManual"
                                    type="text"
                                    placeholder="Ketik manual..."
                                    class="h-10 w-48 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-emerald-400"
                                    @keyup.enter.prevent="kepadaManual && addKepada(kepadaManual)"
                                />
                                <button
                                    type="button"
                                    class="flex items-center gap-1 rounded-xl bg-slate-100 px-3 text-xs font-medium text-slate-600 hover:bg-slate-200 transition-colors"
                                    @click="kepadaManual && addKepada(kepadaManual)"
                                >
                                    <Plus class="size-3.5" /> Tambah
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown preset -->
                        <div
                            v-if="showKepadaDropdown && (filteredPresets.length > 0 || kepadaSearch)"
                            class="absolute left-0 top-full z-10 mt-1 w-72 rounded-xl border border-slate-200 bg-white shadow-lg"
                        >
                            <div class="max-h-48 overflow-y-auto py-1">
                                <button
                                    v-for="p in filteredPresets"
                                    :key="p"
                                    type="button"
                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm hover:bg-emerald-50 transition-colors"
                                    @mousedown.prevent="addKepada(p)"
                                >
                                    <Plus class="size-3.5 text-emerald-500 shrink-0" />
                                    <span class="text-slate-700">{{ p }}</span>
                                </button>
                                <div v-if="filteredPresets.length === 0" class="px-4 py-3 text-xs text-slate-400">
                                    Tidak ada preset. Gunakan input manual.
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.kepada_yth" class="mt-1 text-xs text-red-500">{{ (form.errors as any).kepada_yth }}</p>
                </div>

                <!-- Field dinamis -->
                <div v-if="jenisSurat.field_config.filter(f => f.type !== 'recipient').length > 0"
                    class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-4 text-sm font-semibold text-slate-800">Data Surat</h3>
                    <div class="space-y-4">
                        <div
                            v-for="field in jenisSurat.field_config.filter(f => f.type !== 'recipient')"
                            :key="field.name"
                        >
                            <!-- Textarea -->
                            <label v-if="field.type === 'textarea'" class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </span>
                                <textarea
                                    v-model="form.data[field.name] as string"
                                    rows="4"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                    :class="fieldError(field.name) ? 'border-red-300 bg-red-50' : ''"
                                    :placeholder="field.placeholder ?? ''"
                                />
                                <p v-if="field.help" class="text-xs text-slate-400">{{ field.help }}</p>
                                <p v-if="fieldError(field.name)" class="text-xs text-red-500">{{ fieldError(field.name) }}</p>
                            </label>

                            <!-- Select -->
                            <label v-else-if="field.type === 'select'" class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </span>
                                <select
                                    v-model="form.data[field.name] as string"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400"
                                    :class="fieldError(field.name) ? 'border-red-300' : ''"
                                >
                                    <option value="">— Pilih —</option>
                                    <option v-for="opt in field.options" :key="opt.value ?? opt.label" :value="opt.value ?? opt.label">
                                        {{ opt.label }}
                                    </option>
                                </select>
                                <p v-if="fieldError(field.name)" class="text-xs text-red-500">{{ fieldError(field.name) }}</p>
                            </label>

                            <!-- Checkbox -->
                            <label v-else-if="field.type === 'checkbox'" class="flex items-center gap-2">
                                <input
                                    v-model="form.data[field.name] as boolean"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-emerald-600"
                                />
                                <span class="text-sm text-slate-700">{{ field.label }}</span>
                            </label>

                            <!-- Date -->
                            <label v-else-if="field.type === 'date'" class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </span>
                                <input
                                    v-model="form.data[field.name] as string"
                                    type="date"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400"
                                    :class="fieldError(field.name) ? 'border-red-300' : ''"
                                />
                                <p v-if="fieldError(field.name)" class="text-xs text-red-500">{{ fieldError(field.name) }}</p>
                            </label>

                            <!-- Number -->
                            <label v-else-if="field.type === 'number'" class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </span>
                                <input
                                    v-model="form.data[field.name] as string"
                                    type="number"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400"
                                    :class="fieldError(field.name) ? 'border-red-300' : ''"
                                    :placeholder="field.placeholder ?? ''"
                                />
                                <p v-if="fieldError(field.name)" class="text-xs text-red-500">{{ fieldError(field.name) }}</p>
                            </label>

                            <!-- Repeatable -->
                            <div v-else-if="field.type === 'repeatable'" class="space-y-2">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </span>
                                <div v-for="(_, idx) in (form.data[field.name] as string[])" :key="idx" class="flex gap-2">
                                    <input
                                        v-model="(form.data[field.name] as string[])[idx]"
                                        type="text"
                                        class="h-9 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-emerald-400"
                                        :placeholder="`${field.item_label ?? 'Item'} ${idx + 1}`"
                                    />
                                    <button type="button" class="text-slate-400 hover:text-red-500" @click="removeRepeatItem(field.name, idx)">
                                        <X class="size-4" />
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    class="flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700"
                                    @click="addRepeatItem(field.name, field.add_label ?? 'Tambah')"
                                >
                                    <Plus class="size-3.5" /> {{ field.add_label ?? 'Tambah Item' }}
                                </button>
                            </div>

                            <!-- Text (default) -->
                            <label v-else class="block space-y-1.5">
                                <span class="text-xs font-medium text-slate-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </span>
                                <input
                                    v-model="form.data[field.name] as string"
                                    type="text"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                    :class="fieldError(field.name) ? 'border-red-300 bg-red-50' : ''"
                                    :placeholder="field.placeholder ?? ''"
                                />
                                <p v-if="field.help" class="text-xs text-slate-400">{{ field.help }}</p>
                                <p v-if="fieldError(field.name)" class="text-xs text-red-500">{{ fieldError(field.name) }}</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tombol navigasi -->
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4">
                    <a href="/admin/surat/create" class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        <ChevronLeft class="size-4" /> Kembali
                    </a>
                    <button
                        type="submit"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memproses...' : 'Lanjut ke Preview' }}
                        <ChevronRight v-if="!form.processing" class="size-4" />
                    </button>
                </div>
            </form>

            <!-- Panel kanan -->
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">Info Surat</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Jenis</span>
                            <span class="font-medium text-slate-700 text-right max-w-[160px]">{{ jenisSurat.nama }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Approval</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :class="jenisSurat.perlu_approval ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'">
                                {{ jenisSurat.perlu_approval ? 'Diperlukan' : 'Tidak Perlu' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">QR Code</span>
                            <span class="text-slate-700">
                                {{ jenisSurat.qr_mode === 'immediate' ? 'Langsung aktif' : 'Setelah approve' }}
                            </span>
                        </div>
                        <div v-if="nomorPreview" class="flex justify-between">
                            <span class="text-slate-400">No. Surat</span>
                            <span class="font-mono text-[10px] text-slate-700">{{ nomorPreview }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">Panduan Field</h3>
                    <div class="space-y-2">
                        <div v-for="field in jenisSurat.field_config.slice(0, 6)" :key="field.name"
                            class="flex items-center gap-2">
                            <span class="size-1.5 rounded-full shrink-0"
                                :class="field.required ? 'bg-red-400' : 'bg-slate-300'" />
                            <span class="text-xs text-slate-600">{{ field.label }}</span>
                            <span v-if="field.required" class="ml-auto text-[10px] text-red-400">wajib</span>
                        </div>
                    </div>
                    <p v-if="jenisSurat.field_config.length > 6" class="mt-2 text-[10px] text-slate-400">
                        +{{ jenisSurat.field_config.length - 6 }} field lainnya
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
