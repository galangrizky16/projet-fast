<script setup lang="ts">
// File: resources/js/pages/admin/letters/Create.vue
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Search, X, FileText, ChevronRight, AlertCircle } from 'lucide-vue-next';

type JenisSurat = {
    id: number; nama: string; slug?: string | null;
    kode_surat?: string | null; deskripsi?: string | null;
    perlu_approval?: boolean; qr_mode?: string;
    category?: { id?: number | null; nama?: string | null; warna?: string | null; icon?: string | null } | null;
};

type Category = { id: number; nama: string; warna?: string | null; icon?: string | null };

const props = defineProps<{
    jenisSurats: JenisSurat[];
    categories: Category[];
}>();

const form = useForm({ jenis_surat_id: '' });
const searchQuery   = ref('');
const activeCategory = ref<number | null>(null);

const filtered = computed(() => {
    let list = props.jenisSurats;
    if (activeCategory.value !== null) {
        list = list.filter(j => j.category?.id === activeCategory.value);
    }
    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        list = list.filter(j =>
            j.nama.toLowerCase().includes(q) ||
            (j.kode_surat ?? '').toLowerCase().includes(q) ||
            (j.category?.nama ?? '').toLowerCase().includes(q) ||
            (j.deskripsi ?? '').toLowerCase().includes(q)
        );
    }
    return list;
});

const selected = computed(() => props.jenisSurats.find(j => String(j.id) === form.jenis_surat_id) ?? null);

const colorMap: Record<string, string> = {
    indigo: 'bg-indigo-50 text-indigo-600 border-indigo-200',
    emerald: 'bg-emerald-50 text-emerald-600 border-emerald-200',
    amber: 'bg-amber-50 text-amber-600 border-amber-200',
    blue: 'bg-blue-50 text-blue-600 border-blue-200',
    rose: 'bg-rose-50 text-rose-600 border-rose-200',
    violet: 'bg-violet-50 text-violet-600 border-violet-200',
    cyan: 'bg-cyan-50 text-cyan-600 border-cyan-200',
    slate: 'bg-slate-50 text-slate-600 border-slate-200',
};

function catColor(warna?: string | null) {
    return colorMap[warna ?? ''] ?? colorMap['slate'];
}

function submit() {
    form.post('/admin/surat/select-type');
}
</script>

<template>
    <AdminLayout
        title="Buat Surat"
        subtitle="Pilih jenis surat yang akan dibuat"
        active-menu="letters.create"
        :breadcrumbs="[{ label: 'Buat Surat' }]"
    >
        <Head title="Buat Surat" />

        <!-- Step indicator -->
        <div class="mb-6 flex items-center gap-0 rounded-2xl border border-slate-200 bg-white px-6 py-4">
            <div v-for="(step, i) in [
                { n: 1, label: 'Pilih Jenis Surat' },
                { n: 2, label: 'Isi Data Surat' },
                { n: 3, label: 'Preview & Submit' },
            ]" :key="step.n" class="flex items-center gap-0 flex-1">
                <div class="flex items-center gap-2 shrink-0">
                    <div class="grid size-7 place-items-center rounded-full text-xs font-bold"
                        :class="step.n === 1 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400'">
                        {{ step.n }}
                    </div>
                    <span class="hidden text-xs font-medium sm:block" :class="step.n === 1 ? 'text-emerald-700' : 'text-slate-400'">
                        {{ step.label }}
                    </span>
                </div>
                <div v-if="i < 2" class="mx-3 flex-1 h-px bg-slate-200" />
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_320px]">

            <!-- Kiri: Search + list -->
            <div class="space-y-4">

                <!-- Search box -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="relative">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Ketik nama surat, kode, atau kategori..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 py-0 pl-10 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                        />
                        <button v-if="searchQuery" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" @click="searchQuery = ''">
                            <X class="size-4" />
                        </button>
                    </div>

                    <!-- Kategori pills -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full border px-3 py-1 text-xs font-medium transition-colors"
                            :class="activeCategory === null ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-200 text-slate-500 hover:border-slate-300'"
                            @click="activeCategory = null"
                        >
                            Semua ({{ jenisSurats.length }})
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            type="button"
                            class="rounded-full border px-3 py-1 text-xs font-medium transition-colors"
                            :class="activeCategory === cat.id
                                ? 'bg-slate-900 text-white border-slate-900'
                                : 'border-slate-200 text-slate-500 hover:border-slate-300'"
                            @click="activeCategory = activeCategory === cat.id ? null : cat.id"
                        >
                            {{ cat.nama }}
                        </button>
                    </div>
                </div>

                <!-- Hasil -->
                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-3">
                        <p class="text-xs text-slate-400">
                            <span class="font-semibold text-slate-700">{{ filtered.length }}</span>
                            jenis surat{{ searchQuery ? ` untuk "${searchQuery}"` : '' }}
                        </p>
                    </div>

                    <div v-if="filtered.length === 0" class="flex flex-col items-center gap-2 px-5 py-10 text-center">
                        <AlertCircle class="size-8 text-slate-300" />
                        <p class="text-sm text-slate-400">Tidak ada jenis surat yang cocok.</p>
                        <button type="button" class="text-xs text-emerald-600 hover:underline" @click="searchQuery = ''; activeCategory = null">Hapus filter</button>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <button
                            v-for="jenis in filtered"
                            :key="jenis.id"
                            type="button"
                            class="flex w-full items-center gap-4 px-5 py-4 text-left transition-colors hover:bg-slate-50"
                            :class="String(jenis.id) === form.jenis_surat_id ? 'bg-emerald-50' : ''"
                            @click="form.jenis_surat_id = String(jenis.id)"
                        >
                            <!-- Icon -->
                            <div
                                class="grid size-10 shrink-0 place-items-center rounded-xl border"
                                :class="String(jenis.id) === form.jenis_surat_id
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : catColor(jenis.category?.warna)"
                            >
                                <FileText class="size-5" />
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ jenis.nama }}</p>
                                    <span v-if="jenis.kode_surat" class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-500">
                                        {{ jenis.kode_surat }}
                                    </span>
                                </div>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <p class="text-xs text-slate-400">{{ jenis.category?.nama ?? 'Tanpa Kategori' }}</p>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                        :class="jenis.perlu_approval ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'"
                                    >
                                        {{ jenis.perlu_approval ? 'Perlu Approval' : 'Langsung Selesai' }}
                                    </span>
                                </div>
                            </div>

                            <ChevronRight class="size-4 shrink-0 text-slate-300" :class="String(jenis.id) === form.jenis_surat_id ? 'text-emerald-500' : ''" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kanan: Surat terpilih + tombol lanjut -->
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Surat Dipilih</h3>

                    <div v-if="selected" class="space-y-3">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-medium text-emerald-600">{{ selected.category?.nama ?? 'Tanpa Kategori' }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ selected.nama }}</p>
                            <p v-if="selected.kode_surat" class="mt-0.5 font-mono text-xs text-slate-400">{{ selected.kode_surat }}</p>
                            <p v-if="selected.deskripsi" class="mt-2 text-xs leading-5 text-slate-500">{{ selected.deskripsi }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-slate-50 p-3 text-center">
                                <p class="text-[10px] text-slate-400">Approval</p>
                                <p class="mt-1 text-xs font-semibold" :class="selected.perlu_approval ? 'text-amber-700' : 'text-emerald-700'">
                                    {{ selected.perlu_approval ? 'Diperlukan' : 'Tidak Perlu' }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 text-center">
                                <p class="text-[10px] text-slate-400">QR Code</p>
                                <p class="mt-1 text-xs font-semibold text-slate-700">
                                    {{ selected.qr_mode === 'immediate' ? 'Langsung' : 'Setelah Approve' }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors disabled:opacity-50"
                            :disabled="form.processing"
                            @click="submit"
                        >
                            {{ form.processing ? 'Memproses...' : 'Lanjutkan ke Form Surat' }}
                            <ChevronRight v-if="!form.processing" class="size-4" />
                        </button>
                    </div>

                    <div v-else class="flex flex-col items-center gap-2 py-6 text-center">
                        <FileText class="size-10 text-slate-200" />
                        <p class="text-sm text-slate-400">Pilih jenis surat dari daftar</p>
                        <p class="text-xs text-slate-300">Gunakan search atau filter kategori</p>
                    </div>
                </div>

                <!-- Info panel -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Panduan</h3>
                    <ol class="space-y-2.5 text-xs text-slate-500">
                        <li class="flex gap-2"><span class="size-4 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0">1</span>Cari jenis surat menggunakan kolom search</li>
                        <li class="flex gap-2"><span class="size-4 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0">2</span>Klik kartu surat untuk memilihnya</li>
                        <li class="flex gap-2"><span class="size-4 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0">3</span>Klik "Lanjutkan" untuk mengisi form surat</li>
                        <li class="flex gap-2"><span class="size-4 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center shrink-0">4</span>Preview dan submit untuk dikirim ke approval</li>
                    </ol>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
