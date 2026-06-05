<script setup lang="ts">
// File: resources/js/pages/admin/letters/Form.vue
// Menggantikan: pages/fast/administrasi/surat/Form.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LetterStepIndicator from '@/components/admin/LetterStepIndicator.vue';
import DynamicField from '@/components/admin/DynamicField.vue';
import type { DynamicField as DynamicFieldType } from '@/components/admin/DynamicField.vue';

type JenisSuratPayload = {
    id: number;
    nama: string;
    deskripsi?: string | null;
    category?: { nama?: string | null } | null;
    approval_role?: { nama?: string | null } | null;
    field_config: DynamicFieldType[];
};

type FormDataPayload = {
    jenis_surat_id: number;
    keperluan: string;
    data: Record<string, string | boolean | string[]>;
};

const props = defineProps<{
    jenisSurat: JenisSuratPayload;
    formData: FormDataPayload;
}>();

const form = useForm({
    jenis_surat_id: props.formData.jenis_surat_id,
    keperluan: props.formData.keperluan,
    values: { ...props.formData.data },
});

function fieldError(name: string): string {
    return (form.errors as Record<string, string>)[`data.${name}`] ?? '';
}

function updateField(name: string, value: string | boolean | string[]): void {
    form.values[name] = value;
}

function submitPreview(): void {
    form.transform((current) => ({
        jenis_surat_id: current.jenis_surat_id,
        keperluan: current.keperluan,
        data: current.values,
    })).post('/admin/surat/preview', {
        preserveScroll: true,
        onFinish: () => form.transform((c) => c),
    });
}
</script>

<template>
    <AdminLayout
        :title="jenisSurat.nama"
        :subtitle="jenisSurat.category?.nama ?? 'Pembuatan Surat'"
        active-menu="letters.create"
        :breadcrumbs="[
            { label: 'Buat Surat', href: '/admin/surat/create' },
            { label: jenisSurat.nama },
        ]"
    >
        <Head :title="`Form — ${jenisSurat.nama}`" />

        <div class="mx-auto max-w-3xl space-y-5">
            <LetterStepIndicator :current-step="2" />

            <!-- Info surat -->
            <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ jenisSurat.category?.nama }}</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ jenisSurat.nama }}</h2>
                        <p v-if="jenisSurat.deskripsi" class="mt-1 text-sm text-slate-500">{{ jenisSurat.deskripsi }}</p>
                    </div>
                    <div class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 py-2 text-right text-xs text-slate-500">
                        {{ jenisSurat.approval_role?.nama ? `Approval: ${jenisSurat.approval_role.nama}` : 'Tanpa approval' }}
                    </div>
                </div>
            </div>

            <form class="space-y-5" @submit.prevent="submitPreview">

                <!-- Keperluan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="space-y-1.5">
                        <Label for="keperluan" class="text-sm font-medium text-slate-700">
                            Perihal / Keperluan <span class="text-rose-500">*</span>
                        </Label>
                        <Input
                            id="keperluan"
                            v-model="form.keperluan"
                            type="text"
                            placeholder="Contoh: Permohonan dispensasi untuk kegiatan organisasi"
                            class="h-11 rounded-2xl border-slate-200"
                        />
                        <p v-if="form.errors.keperluan" class="text-xs text-rose-600">{{ form.errors.keperluan }}</p>
                    </div>
                </div>

                <!-- Field Dinamis -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-4 text-sm font-semibold text-slate-700">Data Surat</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <DynamicField
                            v-for="field in jenisSurat.field_config"
                            :key="field.name"
                            :field="field"
                            :model-value="form.values[field.name]"
                            :error="fieldError(field.name)"
                            :class="['textarea', 'recipient'].includes(field.type) || field.name.includes('alasan') || field.name.includes('keterangan') ? 'sm:col-span-2' : ''"
                            @update:model-value="(val) => updateField(field.name, val)"
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Button as-child type="button" variant="outline" class="h-11 rounded-2xl border-slate-200">
                        <Link href="/admin/surat/create">← Kembali</Link>
                    </Button>
                    <Button type="submit" class="h-11 rounded-2xl bg-indigo-600 px-6 text-white hover:bg-indigo-700" :disabled="form.processing">
                        {{ form.processing ? 'Memproses...' : 'Preview Surat →' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
