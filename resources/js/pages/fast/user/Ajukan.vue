<script setup lang="ts">
// resources/js/pages/fast/user/Ajukan.vue
import FastLayout from '@/layouts/FastLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import {
    FileText, UploadCloud, X, Send, ChevronDown, Paperclip, CheckCircle2,
} from 'lucide-vue-next';

type FieldOption = { label: string; value: string };
type FieldConfig = {
    name: string; label: string; type: string;
    required: boolean; placeholder: string; options: FieldOption[];
};
type JenisSuratOption = {
    id: number; categoryId?: number | null;
    nama: string; slug?: string | null; deskripsi?: string | null;
    fieldConfig?: FieldConfig[];
};
type CategoryOption = { id: number; nama: string; slug: string; deskripsi?: string | null };

const props = defineProps<{
    categories: CategoryOption[];
    jenisSurats: JenisSuratOption[];
    selectedJenisId?: number | null;
}>();

type FieldValue = string | boolean | string[] | null;

const form = useForm<{
    jenis_surat_id: string;
    keperluan: string;
    tanggal_kebutuhan: string;
    field_data: Record<string, FieldValue>;
    lampiran: File[];
}>({
    jenis_surat_id: '',
    keperluan: '',
    tanggal_kebutuhan: '',
    field_data: {},
    lampiran: [],
});

const selectedJenis = computed<JenisSuratOption | null>(
    () => props.jenisSurats.find(j => String(j.id) === form.jenis_surat_id) ?? null,
);

function categoryName(id?: number | null): string {
    if (!id) return 'Lainnya';
    return props.categories.find(c => c.id === id)?.nama ?? 'Lainnya';
}

// Group jenis surat by category for the dropdown.
const groupedJenis = computed(() => {
    const groups = new Map<string, JenisSuratOption[]>();
    for (const j of props.jenisSurats) {
        const key = categoryName(j.categoryId);
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key)!.push(j);
    }
    return Array.from(groups.entries()).map(([nama, items]) => ({ nama, items }));
});

function initFieldData(jenis: JenisSuratOption | null) {
    const values: Record<string, FieldValue> = {};
    for (const f of jenis?.fieldConfig ?? []) {
        if (f.type === 'checkbox') values[f.name] = false;
        else if (['checkbox-group', 'multiselect'].includes(f.type)) values[f.name] = [];
        else values[f.name] = '';
    }
    form.field_data = values;
}

function onJenisChange() {
    form.clearErrors();
    initFieldData(selectedJenis.value);
}

onMounted(() => {
    if (props.selectedJenisId) {
        form.jenis_surat_id = String(props.selectedJenisId);
        initFieldData(selectedJenis.value);
    }
});

// ── Lampiran ────────────────────────────────────────────────────────────────
const fileInput = ref<HTMLInputElement | null>(null);

function triggerFilePick() {
    fileInput.value?.click();
}

function onFilesPicked(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;
    form.lampiran = [...form.lampiran, ...Array.from(input.files)];
    input.value = '';
}

function removeFile(index: number) {
    form.lampiran = form.lampiran.filter((_, i) => i !== index);
}

function fileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function todayString() {
    return new Date().toISOString().slice(0, 10);
}

function submit() {
    form.post('/fast/user/submissions', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => router.visit('/fast/user/history'),
    });
}

function fieldError(name: string): string | undefined {
    return (form.errors as Record<string, string>)[`field_data.${name}`];
}
</script>

<template>
    <FastLayout
        title="Ajukan Surat"
        subtitle="Lengkapi data pengajuan surat akademik Anda"
        active-menu="submit"
        :breadcrumbs="[{ label: 'Dashboard', href: '/fast/user/dashboard' }, { label: 'Ajukan Surat' }]"
    >
        <Head title="Ajukan Surat — FAST" />

        <form class="mx-auto max-w-2xl space-y-6" @submit.prevent="submit">
            <!-- Step 1: Pilih jenis surat -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid size-8 place-items-center rounded-xl bg-emerald-600 text-white">
                        <FileText class="size-4" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Jenis Surat</p>
                        <p class="text-xs text-slate-400">Pilih jenis surat yang ingin diajukan</p>
                    </div>
                </div>

                <div class="relative">
                    <select
                        v-model="form.jenis_surat_id"
                        required
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 pr-10 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:bg-white"
                        :class="form.errors.jenis_surat_id ? 'border-red-300' : ''"
                        @change="onJenisChange"
                    >
                        <option value="" disabled>— Pilih jenis surat —</option>
                        <optgroup v-for="group in groupedJenis" :key="group.nama" :label="group.nama">
                            <option v-for="j in group.items" :key="j.id" :value="String(j.id)">{{ j.nama }}</option>
                        </optgroup>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                </div>
                <p v-if="form.errors.jenis_surat_id" class="mt-1.5 text-xs text-red-500">{{ form.errors.jenis_surat_id }}</p>

                <p v-if="selectedJenis?.deskripsi" class="mt-3 rounded-xl bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                    {{ selectedJenis.deskripsi }}
                </p>

                <div v-if="jenisSurats.length === 0" class="mt-3 rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-400">
                    Tidak ada jenis surat tersedia untuk akun Anda.
                </div>
            </section>

            <!-- Step 2: Isi data (muncul setelah jenis dipilih) -->
            <template v-if="selectedJenis">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="grid size-8 place-items-center rounded-xl bg-emerald-600 text-white">
                            <CheckCircle2 class="size-4" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Data Pengajuan</p>
                            <p class="text-xs text-slate-400">Isi informasi sesuai kebutuhan surat</p>
                        </div>
                    </div>

                    <!-- Keperluan -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Keperluan <span class="text-red-500">*</span></label>
                        <textarea
                            v-model="form.keperluan"
                            rows="2"
                            placeholder="Jelaskan keperluan pengajuan surat (min. 10 karakter)..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white"
                        />
                        <p v-if="form.errors.keperluan" class="mt-1 text-xs text-red-500">{{ form.errors.keperluan }}</p>
                    </div>

                    <!-- Tanggal kebutuhan -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Tanggal Dibutuhkan <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.tanggal_kebutuhan"
                            type="date"
                            :min="todayString()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-emerald-400 focus:bg-white"
                        />
                        <p v-if="form.errors.tanggal_kebutuhan" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal_kebutuhan }}</p>
                    </div>

                    <!-- Dynamic fields -->
                    <template v-for="field in selectedJenis.fieldConfig ?? []" :key="field.name">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ field.label }}
                                <span v-if="field.required" class="text-red-500">*</span>
                            </label>

                            <textarea v-if="field.type === 'textarea'"
                                v-model="(form.field_data[field.name] as string)"
                                :placeholder="field.placeholder" rows="3"
                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white" />

                            <div v-else-if="field.type === 'select'" class="relative">
                                <select v-model="form.field_data[field.name]"
                                    class="h-10 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-3 pr-9 text-sm text-slate-900 outline-none focus:border-emerald-400 focus:bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            </div>

                            <div v-else-if="field.type === 'radio'" class="flex flex-wrap gap-3 pt-1">
                                <label v-for="opt in field.options" :key="opt.value" class="flex cursor-pointer items-center gap-1.5 text-sm">
                                    <input type="radio" :value="opt.value" v-model="form.field_data[field.name]" class="text-emerald-600" />
                                    {{ opt.label }}
                                </label>
                            </div>

                            <label v-else-if="field.type === 'checkbox'" class="flex cursor-pointer items-center gap-2 pt-1">
                                <input type="checkbox" v-model="form.field_data[field.name]" class="rounded text-emerald-600" />
                                <span class="text-sm text-slate-700">{{ field.placeholder || field.label }}</span>
                            </label>

                            <div v-else-if="['checkbox-group','multiselect'].includes(field.type)" class="flex flex-wrap gap-3 pt-1">
                                <label v-for="opt in field.options" :key="opt.value" class="flex cursor-pointer items-center gap-1.5 text-sm">
                                    <input type="checkbox" :value="opt.value" v-model="(form.field_data[field.name] as string[])" class="rounded text-emerald-600" />
                                    {{ opt.label }}
                                </label>
                            </div>

                            <input v-else
                                v-model="(form.field_data[field.name] as string)"
                                :type="field.type === 'number' ? 'number' : field.type === 'date' ? 'date' : field.type === 'email' ? 'email' : 'text'"
                                :placeholder="field.placeholder"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 outline-none focus:border-emerald-400 focus:bg-white" />

                            <p v-if="fieldError(field.name)" class="mt-1 text-xs text-red-500">{{ fieldError(field.name) }}</p>
                        </div>
                    </template>
                </section>

                <!-- Step 3: Upload lampiran -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="grid size-8 place-items-center rounded-xl bg-emerald-600 text-white">
                            <Paperclip class="size-4" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Lampiran <span class="text-xs font-normal text-slate-400">(opsional)</span></p>
                            <p class="text-xs text-slate-400">PDF, JPG, PNG, DOC — maks. 4 MB / berkas</p>
                        </div>
                    </div>

                    <input ref="fileInput" type="file" multiple class="hidden"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" @change="onFilesPicked" />

                    <button type="button"
                        class="flex w-full flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 py-6 text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50/40"
                        @click="triggerFilePick">
                        <UploadCloud class="size-7 text-slate-400" />
                        <span class="text-sm font-medium">Klik untuk pilih berkas</span>
                    </button>

                    <p v-if="form.errors.lampiran" class="mt-1.5 text-xs text-red-500">{{ form.errors.lampiran }}</p>

                    <ul v-if="form.lampiran.length" class="mt-3 space-y-2">
                        <li v-for="(file, i) in form.lampiran" :key="i"
                            class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                            <FileText class="size-4 shrink-0 text-emerald-600" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-slate-700">{{ file.name }}</p>
                                <p class="text-[10px] text-slate-400">{{ fileSize(file.size) }}</p>
                            </div>
                            <button type="button" class="grid size-7 place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500"
                                @click="removeFile(i)">
                                <X class="size-4" />
                            </button>
                        </li>
                    </ul>
                </section>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3 pb-4">
                    <button type="button"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        @click="router.visit('/fast/user/dashboard')">
                        Batal
                    </button>
                    <button type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50">
                        <Send class="size-4" />
                        {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                    </button>
                </div>
            </template>
        </form>
    </FastLayout>
</template>
