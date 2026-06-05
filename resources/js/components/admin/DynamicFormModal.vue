<script setup lang="ts">
import type { FormDataConvertible } from '@inertiajs/core';
import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { CalendarDays, CloudUpload, LoaderCircle, Paperclip, SendHorizonal, X } from 'lucide-vue-next';

type JenisSuratOption = {
    id: number;
    categoryId?: number | null;
    nama: string;
    slug?: string | null;
    deskripsi?: string | null;
};

type SuratCategoryOption = {
    id: number;
    nama: string;
    slug: string;
    deskripsi?: string | null;
};

type FieldOption = {
    label?: string;
    value: string;
};

type DynamicField = {
    name: string;
    label: string;
    type: string;
    placeholder?: string;
    help?: string;
    required?: boolean;
    options?: FieldOption[];
};

type PageProps = {
    auth: {
        user?: {
            nim_nip?: string | null;
            nomor_induk?: string | null;
            name?: string | null;
            email?: string | null;
        };
    };
};

const props = defineProps<{
    open: boolean;
    categories: SuratCategoryOption[];
    jenisSurats: JenisSuratOption[];
    submitUrl: string;
    jenisSuratBaseUrl: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submitted', message?: string): void;
}>();

const processing = ref(false);
const loadingConfig = ref(false);
const dragActive = ref(false);
const errors = ref<Record<string, string>>({});
const fetchError = ref('');
const fields = ref<DynamicField[]>([]);
const page = usePage<PageProps>();

const form = reactive({
    category_id: '',
    jenis_surat_id: '',
    keperluan: '',
    tanggal_kebutuhan: '',
    values: {} as Record<string, FormDataConvertible>,
    lampiran: [] as File[],
});

const selectedCategory = computed(() =>
    props.categories.find((item) => String(item.id) === form.category_id),
);
const selectedJenisSurat = computed(() =>
    props.jenisSurats.find((item) => String(item.id) === form.jenis_surat_id),
);
const filteredJenisSurats = computed(() =>
    props.jenisSurats.filter((item) => String(item.categoryId ?? '') === form.category_id),
);
const authUser = computed(() => page.props.auth?.user);
const primaryField = computed(() =>
    fields.value.find((field) =>
        ['text', 'email', 'number', 'date'].includes(field.type),
    ),
);
const secondaryFields = computed(() =>
    fields.value.filter((field) => field.name !== primaryField.value?.name),
);
const validationMessages = computed(() =>
    Object.values(errors.value).filter(
        (message): message is string =>
            typeof message === 'string' && message.trim().length > 0,
    ),
);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            resetForm();
        }
    },
);

watch(
    () => form.category_id,
    (value, previousValue) => {
        if (value === previousValue) {
            return;
        }

        const availableJenisSurats = props.jenisSurats.filter(
            (item) => String(item.categoryId ?? '') === value,
        );

        if (!availableJenisSurats.some((item) => String(item.id) === form.jenis_surat_id)) {
            form.jenis_surat_id = '';
        }

        if (availableJenisSurats.length === 1) {
            form.jenis_surat_id = String(availableJenisSurats[0].id);
        }

        if (value === '') {
            fields.value = [];
            form.values = {};
            fetchError.value = '';
            errors.value = {};
        }
    },
);

watch(
    () => form.jenis_surat_id,
    async (value, previousValue) => {
        if (!value || value === previousValue) {
            fields.value = [];
            form.values = {};
            return;
        }

        loadingConfig.value = true;
        fetchError.value = '';
        errors.value = {};

        try {
            const response = await fetch(`${props.jenisSuratBaseUrl}/${value}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Gagal memuat form surat.');
            }

            const payload = (await response.json()) as {
                data?: { field_config?: Array<Record<string, unknown>> };
            };

            fields.value = (payload.data?.field_config ?? []).map((field) => ({
                name: String(field.name ?? ''),
                label: String(field.label ?? field.name ?? 'Field'),
                type: String(field.type ?? 'text').toLowerCase(),
                placeholder: field.placeholder ? String(field.placeholder) : '',
                help: field.help ? String(field.help) : '',
                required: Boolean(field.required ?? false),
                options: Array.isArray(field.options)
                    ? field.options
                          .map((option) =>
                              typeof option === 'object' && option !== null
                                  ? {
                                        label: String(
                                            (option as FieldOption).label ??
                                                (option as FieldOption).value,
                                        ),
                                        value: String(
                                            (option as FieldOption).value ?? '',
                                        ),
                                    }
                                  : {
                                        label: String(option),
                                        value: String(option),
                                    },
                          )
                          .filter((option) => option.value.length > 0)
                    : [],
            }));

            form.values = Object.fromEntries(
                fields.value.map((field) => [
                    field.name,
                    getInitialFieldValue(field),
                ]),
            ) as Record<string, FormDataConvertible>;
        } catch (error) {
            fetchError.value =
                error instanceof Error
                    ? error.message
                    : 'Form dinamis gagal dimuat.';
            fields.value = [];
            form.values = {};
        } finally {
            loadingConfig.value = false;
        }
    },
);

function closeModal() {
    resetForm();
    emit('update:open', false);
}

function handleOpenChange(value: boolean) {
    if (!value) {
        closeModal();
        return;
    }

    emit('update:open', value);
}

function resetForm() {
    processing.value = false;
    loadingConfig.value = false;
    fetchError.value = '';
    errors.value = {};
    fields.value = [];
    form.category_id = '';
    form.jenis_surat_id = '';
    form.keperluan = '';
    form.tanggal_kebutuhan = '';
    form.values = {};
    form.lampiran = [];
}

function setFieldValue(name: string, value: FormDataConvertible) {
    form.values = {
        ...form.values,
        [name]: value,
    };
}

function toggleMultiValue(name: string, value: string, checked: boolean) {
    const current = Array.isArray(form.values[name])
        ? [...(form.values[name] as string[])]
        : [];
    const next = checked
        ? Array.from(new Set([...current, value]))
        : current.filter((item) => item !== value);

    setFieldValue(name, next);
}

function onLampiranChange(event: Event) {
    const target = event.target as HTMLInputElement;
    form.lampiran = Array.from(target.files ?? []);
}

function fieldError(name: string) {
    return errors.value[`data.${name}`];
}

function getInitialFieldValue(field: DynamicField): FormDataConvertible {
    if (field.type === 'checkbox-group' || field.type === 'multiselect') {
        return [];
    }

    if (field.type === 'checkbox') {
        return false;
    }

    const normalized = field.name.toLowerCase();

    if (
        normalized.includes('nim')
        || normalized.includes('nip')
        || normalized.includes('nomor_induk')
    ) {
        return authUser.value?.nim_nip ?? authUser.value?.nomor_induk ?? '';
    }

    if (normalized === 'nama') {
        return authUser.value?.name ?? '';
    }

    if (normalized === 'email') {
        return authUser.value?.email ?? '';
    }

    return '';
}

function onDrop(event: DragEvent) {
    event.preventDefault();
    dragActive.value = false;
    form.lampiran = Array.from(event.dataTransfer?.files ?? []);
}

function removeLampiran(index: number) {
    form.lampiran = form.lampiran.filter((_, itemIndex) => itemIndex !== index);
}

function inputType(field: DynamicField) {
    return ['text', 'email', 'number', 'date'].includes(field.type)
        ? field.type
        : 'text';
}

function submit() {
    processing.value = true;
    errors.value = {};

    router.post(
        props.submitUrl,
        {
            jenis_surat_id: form.jenis_surat_id,
            keperluan: form.keperluan,
            tanggal_kebutuhan: form.tanggal_kebutuhan,
            data: form.values,
            lampiran: form.lampiran,
        },
        {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onError: (formErrors) => {
                errors.value = formErrors as Record<string, string>;
            },
            onSuccess: () => {
                emit('submitted', 'Pengajuan surat berhasil dikirim.');
                closeModal();
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent
            class="flex max-h-[94vh] w-[min(960px,calc(100vw-2rem))] max-w-[960px] flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-0 shadow-[0_30px_80px_rgba(15,23,42,0.18)]"
            :show-close-button="false"
            @close-auto-focus.prevent
        >
            <div class="border-b border-slate-100 px-6 py-5">
                <DialogHeader class="space-y-1 text-left">
                    <DialogTitle class="text-[30px] font-semibold tracking-tight text-slate-900">
                        Ajukan Surat
                    </DialogTitle>
                    <DialogDescription class="text-sm text-slate-500">
                        Lengkapi detail informasi untuk pengajuan surat baru.
                    </DialogDescription>
                </DialogHeader>
                <DialogClose as-child>
                    <button
                        type="button"
                        class="absolute right-5 top-5 rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        :disabled="processing"
                        @click="closeModal"
                    >
                        <X class="size-5" />
                    </button>
                </DialogClose>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                <div class="space-y-6">
                    <div
                        v-if="validationMessages.length > 0"
                        class="rounded-3xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-700"
                    >
                        <p class="font-semibold">
                            Pengajuan belum bisa dikirim
                        </p>
                        <ul class="mt-2 space-y-1">
                            <li
                                v-for="message in validationMessages"
                                :key="message"
                            >
                                - {{ message }}
                            </li>
                        </ul>
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_id" class="text-sm font-semibold text-slate-700">Kategori Surat</Label>
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="h-14 rounded-2xl border border-[#E5ECFF] bg-[#F3F6FF] px-4 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">Pilih kategori surat...</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="String(category.id)"
                            >
                                {{ category.nama }}
                            </option>
                        </select>
                        <p v-if="selectedCategory?.deskripsi" class="text-sm text-slate-500">
                            {{ selectedCategory.deskripsi }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="jenis_surat_id" class="text-sm font-semibold text-slate-700">Jenis Surat</Label>
                        <select
                            id="jenis_surat_id"
                            v-model="form.jenis_surat_id"
                            :disabled="form.category_id === ''"
                            class="h-14 rounded-2xl border border-[#E5ECFF] bg-[#F3F6FF] px-4 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                        >
                            <option value="">
                                {{ form.category_id === '' ? 'Pilih kategori terlebih dahulu...' : 'Pilih jenis surat...' }}
                            </option>
                            <option
                                v-for="jenisSurat in filteredJenisSurats"
                                :key="jenisSurat.id"
                                :value="String(jenisSurat.id)"
                            >
                                {{ jenisSurat.nama }}
                            </option>
                        </select>
                        <p v-if="selectedJenisSurat?.deskripsi" class="text-sm text-slate-500">
                            {{ selectedJenisSurat.deskripsi }}
                        </p>
                        <InputError :message="errors.jenis_surat_id" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2" v-if="primaryField">
                        <div class="grid gap-2">
                            <Label :for="primaryField.name" class="text-sm font-semibold text-slate-700">
                                {{ primaryField.label }}
                            </Label>
                            <Input
                                v-if="primaryField.type !== 'textarea'"
                                :id="primaryField.name"
                                :type="inputType(primaryField)"
                                :model-value="String(form.values[primaryField.name] ?? '')"
                                class="h-14 rounded-2xl border-[#E5ECFF] bg-[#F3F6FF] focus-visible:border-indigo-300 focus-visible:bg-white focus-visible:ring-indigo-100"
                                :placeholder="primaryField.placeholder || 'Contoh: 20210045'"
                                @update:model-value="setFieldValue(primaryField.name, $event)"
                            />
                            <textarea
                                v-else
                                :id="primaryField.name"
                                :value="String(form.values[primaryField.name] ?? '')"
                                rows="3"
                                class="rounded-2xl border border-[#E5ECFF] bg-[#F3F6FF] px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                :placeholder="primaryField.placeholder"
                                @input="setFieldValue(primaryField.name, ($event.target as HTMLTextAreaElement).value)"
                            />
                            <InputError :message="fieldError(primaryField.name)" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tanggal_kebutuhan" class="text-sm font-semibold text-slate-700">
                                Tanggal Dibutuhkan
                            </Label>
                            <div class="relative">
                                <Input
                                    id="tanggal_kebutuhan"
                                    v-model="form.tanggal_kebutuhan"
                                    type="date"
                                    class="h-14 rounded-2xl border-[#E5ECFF] bg-[#F3F6FF] pr-11 focus-visible:border-indigo-300 focus-visible:bg-white focus-visible:ring-indigo-100"
                                />
                                <CalendarDays class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            </div>
                            <InputError :message="errors.tanggal_kebutuhan" />
                        </div>
                    </div>

                    <div class="grid gap-2" v-else>
                        <Label for="tanggal_kebutuhan" class="text-sm font-semibold text-slate-700">
                            Tanggal Dibutuhkan
                        </Label>
                        <div class="relative">
                            <Input
                                id="tanggal_kebutuhan"
                                v-model="form.tanggal_kebutuhan"
                                type="date"
                                class="h-14 rounded-2xl border-[#E5ECFF] bg-[#F3F6FF] pr-11 focus-visible:border-indigo-300 focus-visible:bg-white focus-visible:ring-indigo-100"
                            />
                            <CalendarDays class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        </div>
                        <InputError :message="errors.tanggal_kebutuhan" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="keperluan" class="text-sm font-semibold text-slate-700">Keperluan</Label>
                        <textarea
                            id="keperluan"
                            v-model="form.keperluan"
                            rows="4"
                            class="rounded-2xl border border-[#E5ECFF] bg-[#F3F6FF] px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                            placeholder="Jelaskan secara singkat tujuan pengajuan surat ini..."
                        />
                        <InputError :message="errors.keperluan" />
                    </div>

                    <div
                        v-if="loadingConfig"
                        class="rounded-3xl border border-dashed border-indigo-200 bg-indigo-50 px-4 py-5 text-sm text-indigo-700"
                    >
                        <div class="flex items-center gap-3">
                            <LoaderCircle class="size-4 animate-spin" />
                            <span>Memuat konfigurasi form surat...</span>
                        </div>
                    </div>

                    <div
                        v-else-if="fetchError"
                        class="rounded-3xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-700"
                    >
                        {{ fetchError }}
                    </div>

                    <div v-if="secondaryFields.length > 0" class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Data Tambahan</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Sistem akan menyesuaikan pertanyaan berdasarkan jenis surat yang dipilih.
                            </p>
                        </div>

                        <div
                            v-for="field in secondaryFields"
                            :key="field.name"
                            class="grid gap-2"
                        >
                            <Label :for="field.name">
                                {{ field.label }}
                                <span v-if="field.required" class="text-rose-500">*</span>
                            </Label>

                            <textarea
                                v-if="field.type === 'textarea'"
                                :id="field.name"
                                :value="String(form.values[field.name] ?? '')"
                                rows="4"
                                class="rounded-2xl border border-[#E5ECFF] bg-[#F3F6FF] px-4 py-3 text-sm outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                :placeholder="field.placeholder"
                                @input="setFieldValue(field.name, ($event.target as HTMLTextAreaElement).value)"
                            />

                            <select
                                v-else-if="field.type === 'select'"
                                :id="field.name"
                                :value="String(form.values[field.name] ?? '')"
                                class="h-14 rounded-2xl border border-[#E5ECFF] bg-[#F3F6FF] px-4 text-sm outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                @change="setFieldValue(field.name, ($event.target as HTMLSelectElement).value)"
                            >
                                <option value="">Pilih salah satu</option>
                                <option
                                    v-for="option in field.options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label ?? option.value }}
                                </option>
                            </select>

                            <div
                                v-else-if="field.type === 'radio'"
                                class="grid gap-2 rounded-2xl border border-[#E5ECFF] bg-[#F7F9FF] p-4"
                            >
                                <label
                                    v-for="option in field.options"
                                    :key="option.value"
                                    class="flex items-center gap-3 text-sm text-slate-700"
                                >
                                    <input
                                        type="radio"
                                        :name="field.name"
                                        :value="option.value"
                                        :checked="form.values[field.name] === option.value"
                                        class="size-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        @change="setFieldValue(field.name, option.value)"
                                    >
                                    <span>{{ option.label ?? option.value }}</span>
                                </label>
                            </div>

                            <div
                                v-else-if="field.type === 'checkbox-group' || field.type === 'multiselect'"
                                class="grid gap-2 rounded-2xl border border-[#E5ECFF] bg-[#F7F9FF] p-4"
                            >
                                <label
                                    v-for="option in field.options"
                                    :key="option.value"
                                    class="flex items-center gap-3 text-sm text-slate-700"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="Array.isArray(form.values[field.name]) && (form.values[field.name] as string[]).includes(option.value)"
                                        class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        @change="toggleMultiValue(field.name, option.value, ($event.target as HTMLInputElement).checked)"
                                    >
                                    <span>{{ option.label ?? option.value }}</span>
                                </label>
                            </div>

                            <label
                                v-else-if="field.type === 'checkbox'"
                                class="flex items-center gap-3 rounded-2xl border border-[#E5ECFF] bg-[#F7F9FF] px-4 py-3 text-sm text-slate-700"
                            >
                                <input
                                    type="checkbox"
                                    :checked="Boolean(form.values[field.name])"
                                    class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    @change="setFieldValue(field.name, ($event.target as HTMLInputElement).checked)"
                                >
                                <span>{{ field.placeholder || field.label }}</span>
                            </label>

                            <Input
                                v-else
                                :id="field.name"
                                :type="['text', 'email', 'number', 'date'].includes(field.type) ? field.type : 'text'"
                                :model-value="String(form.values[field.name] ?? '')"
                                class="h-14 rounded-2xl border-[#E5ECFF] bg-[#F3F6FF] focus-visible:border-indigo-300 focus-visible:bg-white focus-visible:ring-indigo-100"
                                :placeholder="field.placeholder"
                                @update:model-value="setFieldValue(field.name, $event)"
                            />

                            <p v-if="field.help" class="text-xs text-slate-500">
                                {{ field.help }}
                            </p>
                            <InputError :message="fieldError(field.name)" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="lampiran" class="text-sm font-semibold text-slate-700">
                            Lampiran Pendukung
                            <span class="text-slate-400">(opsional)</span>
                        </Label>
                        <label
                            for="lampiran"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-[28px] border border-dashed px-6 py-8 text-center transition"
                            :class="
                                dragActive
                                    ? 'border-indigo-400 bg-indigo-50'
                                    : 'border-[#CBD5F0] bg-white hover:border-indigo-300 hover:bg-[#F8FAFF]'
                            "
                            @dragenter.prevent="dragActive = true"
                            @dragover.prevent="dragActive = true"
                            @dragleave.prevent="dragActive = false"
                            @drop="onDrop"
                        >
                            <div class="grid size-14 place-items-center rounded-full bg-indigo-100 text-indigo-600">
                                <CloudUpload class="size-6" />
                            </div>
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-slate-700">
                                    Klik atau seret file ke sini
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    PDF, JPG atau PNG (maks. 5MB)
                                </p>
                            </div>
                        </label>
                        <input id="lampiran" type="file" multiple class="hidden" @change="onLampiranChange">
                        <ul
                            v-if="form.lampiran.length > 0"
                            class="space-y-2 text-sm text-slate-500"
                        >
                            <li
                                v-for="(file, index) in form.lampiran"
                                :key="`${file.name}-${file.lastModified}`"
                                class="flex items-center justify-between rounded-2xl bg-[#F3F6FF] px-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <Paperclip class="size-4 text-indigo-500" />
                                    <div>
                                        <p class="font-medium text-slate-700">{{ file.name }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ (file.size / 1024 / 1024).toFixed(2) }} MB
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-full p-1 text-slate-400 transition hover:bg-white hover:text-slate-700"
                                    @click="removeLampiran(index)"
                                >
                                    <X class="size-4" />
                                </button>
                            </li>
                        </ul>
                        <InputError :message="errors.lampiran || errors['lampiran.0']" />
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 border-t border-slate-100 bg-white px-6 py-5 shadow-[0_-12px_24px_rgba(15,23,42,0.04)]">
                <DialogClose as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        class="rounded-full px-5 text-slate-500"
                        :disabled="processing"
                        @click="closeModal"
                    >
                        Batal
                    </Button>
                </DialogClose>
                <Button
                    type="button"
                    class="h-12 rounded-xl bg-indigo-600 px-6 text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700"
                    :disabled="processing || loadingConfig"
                    @click="submit"
                >
                    <LoaderCircle v-if="processing" class="mr-2 size-4 animate-spin" />
                    <SendHorizonal v-else class="mr-2 size-4" />
                    Kirim Pengajuan
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
