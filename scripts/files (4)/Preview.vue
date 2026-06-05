<script setup lang="ts">
// resources/js/pages/admin/letters/Preview.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ChevronLeft, Send, Eye, Printer } from 'lucide-vue-next';

type JenisSurat = {
    id: number; nama: string; kode_surat?: string | null;
    perlu_approval: boolean; qr_mode?: string;
    category?: { nama?: string | null } | null;
};

type PreviewData = {
    keperluan: string;
    kepada_yth: string[];
    lampiran_keterangan?: string | null;
    data: Record<string, string>;
};

const props = defineProps<{
    jenisSurat: JenisSurat;
    previewHtml: string;
    nomorPreview?: string | null;
    payload: PreviewData;
}>();

const form = useForm({
    jenis_surat_id: props.jenisSurat.id,
    keperluan: props.payload.keperluan,
    kepada_yth: props.payload.kepada_yth,
    lampiran_keterangan: props.payload.lampiran_keterangan ?? '',
    data: props.payload.data,
});

const showFullPreview = ref(false);

function submit() {
    form.post('/admin/surat/store');
}

function goBack() {
    window.history.back();
}
</script>

<template>
    <AdminLayout
        title="Preview Surat"
        :subtitle="`${jenisSurat.category?.nama ?? 'Surat'} — ${jenisSurat.nama}`"
        active-menu="letters.create"
        :breadcrumbs="[
            { label: 'Buat Surat', href: '/admin/surat/create' },
            { label: 'Isi Form', href: '/admin/surat/form/' + jenisSurat.id },
            { label: 'Preview' },
        ]"
    >
        <Head :title="`Preview — ${jenisSurat.nama}`" />

        <!-- Step indicator -->
        <div class="mb-6 flex items-center gap-0 rounded-2xl border border-slate-200 bg-white px-6 py-4">
            <div v-for="(step, i) in [
                { n: 1, label: 'Pilih Jenis Surat', done: true },
                { n: 2, label: 'Isi Data Surat', done: true },
                { n: 3, label: 'Preview & Submit', active: true },
            ]" :key="step.n" class="flex items-center gap-0 flex-1">
                <div class="flex items-center gap-2 shrink-0">
                    <div class="grid size-7 place-items-center rounded-full text-xs font-bold"
                        :class="step.done ? 'bg-emerald-600 text-white' : step.active ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400'">
                        <span v-if="step.done && !step.active">✓</span>
                        <span v-else>{{ step.n }}</span>
                    </div>
                    <span class="hidden text-xs font-medium sm:block"
                        :class="step.active || step.done ? 'text-emerald-700' : 'text-slate-400'">
                        {{ step.label }}
                    </span>
                </div>
                <div v-if="i < 2" class="mx-3 flex-1 h-px" :class="step.done ? 'bg-emerald-300' : 'bg-slate-200'" />
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_300px]">

            <!-- Preview surat -->
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <Eye class="size-4 text-slate-400" />
                            <h3 class="text-sm font-semibold text-slate-800">Preview Dokumen</h3>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50 transition-colors"
                                @click="showFullPreview = !showFullPreview"
                            >
                                {{ showFullPreview ? 'Tampilan Ringkas' : 'Tampilan Penuh' }}
                            </button>
                        </div>
                    </div>

                    <!-- Iframe preview -->
                    <div
                        class="overflow-hidden transition-all"
                        :class="showFullPreview ? 'h-[800px]' : 'h-[500px]'"
                    >
                        <iframe
                            class="h-full w-full border-0"
                            :srcdoc="previewHtml"
                            sandbox="allow-same-origin"
                            title="Preview surat"
                        />
                    </div>
                </div>

                <!-- Navigasi -->
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                        @click="goBack"
                    >
                        <ChevronLeft class="size-4" /> Kembali Edit
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors disabled:opacity-50"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        <Send v-if="!form.processing" class="size-4" />
                        {{ form.processing ? 'Menyimpan...' : (jenisSurat.perlu_approval ? 'Ajukan Surat' : 'Buat & Generate Surat') }}
                    </button>
                </div>
            </div>

            <!-- Panel kanan — ringkasan data -->
            <div class="space-y-4">

                <!-- Ringkasan -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">Ringkasan Surat</h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <p class="text-slate-400">Jenis Surat</p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ jenisSurat.nama }}</p>
                        </div>
                        <div v-if="nomorPreview">
                            <p class="text-slate-400">Nomor Surat (estimasi)</p>
                            <p class="mt-0.5 font-mono text-slate-800">{{ nomorPreview }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400">Keperluan</p>
                            <p class="mt-0.5 text-slate-700">{{ payload.keperluan }}</p>
                        </div>
                        <div v-if="payload.kepada_yth.length > 0">
                            <p class="text-slate-400">Kepada Yth.</p>
                            <div class="mt-1 space-y-1">
                                <p v-for="k in payload.kepada_yth" :key="k" class="text-slate-700">• {{ k }}</p>
                            </div>
                        </div>
                        <div v-if="payload.lampiran_keterangan">
                            <p class="text-slate-400">Lampiran</p>
                            <p class="mt-0.5 text-slate-700">{{ payload.lampiran_keterangan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status flow -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">Alur Setelah Submit</h3>
                    <div class="space-y-2">
                        <div v-for="(step, i) in jenisSurat.perlu_approval
                            ? ['Surat diajukan', 'Validasi Admin', 'Approval', 'Generate PDF & QR']
                            : ['Surat dibuat', 'Generate PDF & QR', 'Selesai']"
                            :key="step"
                            class="flex items-center gap-2"
                        >
                            <div class="grid size-5 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500">
                                {{ i + 1 }}
                            </div>
                            <span class="text-xs text-slate-600">{{ step }}</span>
                        </div>
                    </div>

                    <div class="mt-3 rounded-xl p-3 text-xs"
                        :class="jenisSurat.perlu_approval ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'">
                        <span v-if="jenisSurat.perlu_approval">
                            ⚠ Surat ini memerlukan approval sebelum bisa diunduh.
                        </span>
                        <span v-else>
                            ✓ Surat akan langsung di-generate setelah submit.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
