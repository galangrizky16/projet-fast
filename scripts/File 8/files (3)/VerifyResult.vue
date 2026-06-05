<script setup lang="ts">
// File: resources/js/pages/qr/VerifyResult.vue
// Halaman publik - tidak pakai AdminLayout
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    found:   boolean;
    valid:   boolean;
    revoked: boolean;
    message: string;
    surat: {
        nomor_surat?: string | null;
        jenis_surat?: string | null;
        kategori?: string | null;
        status?: string | null;
        nama_pemohon?: string | null;
        nim?: string | null;
        program_studi?: string | null;
        keperluan?: string | null;
        tanggal?: string | null;
        tanggal_selesai?: string | null;
        approved_by?: string | null;
        approved_at?: string | null;
        qr_activated_at?: string | null;
    } | null;
}>();

const state = computed(() => {
    if (!props.found) return 'not_found';
    if (props.revoked) return 'revoked';
    if (props.valid) return 'valid';
    return 'pending';
});

const stateConfig = {
    valid:     { title: 'Dokumen Valid', color: 'emerald', icon: '✓', bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', banner: 'bg-emerald-50 border-emerald-200' },
    revoked:   { title: 'Dokumen Dicabut', color: 'red', icon: '✗', bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', banner: 'bg-red-50 border-red-200' },
    pending:   { title: 'Belum Final', color: 'amber', icon: '⏳', bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-200', banner: 'bg-amber-50 border-amber-200' },
    not_found: { title: 'Tidak Ditemukan', color: 'slate', icon: '?', bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-200', banner: 'bg-slate-50 border-slate-200' },
};

const cfg = computed(() => stateConfig[state.value]);
</script>

<template>
    <Head title="Verifikasi Dokumen — FMIKOM" />

    <div class="min-h-screen bg-slate-50 px-4 py-10">
        <div class="mx-auto max-w-lg space-y-5">

            <!-- Header institusi -->
            <div class="text-center">
                <div class="mx-auto mb-3 grid size-14 place-items-center rounded-2xl bg-emerald-600 text-2xl text-white shadow-sm">
                    📄
                </div>
                <h1 class="text-lg font-bold text-slate-900">Verifikasi Dokumen</h1>
                <p class="text-sm text-slate-400">Fakultas Matematika dan Ilmu Komputer</p>
                <p class="text-xs text-slate-300">Universitas NU Al Ghazali Cilacap</p>
            </div>

            <!-- Status card -->
            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm" :class="cfg.border">

                <!-- Banner status -->
                <div class="flex items-center gap-3 border-b px-5 py-4" :class="cfg.banner">
                    <div class="grid size-10 shrink-0 place-items-center rounded-xl text-lg font-bold" :class="[cfg.bg, cfg.text]">
                        {{ cfg.icon }}
                    </div>
                    <div>
                        <p class="font-semibold" :class="cfg.text">{{ cfg.title }}</p>
                        <p class="text-xs" :class="cfg.text">{{ message }}</p>
                    </div>
                </div>

                <!-- Data surat (jika ditemukan) -->
                <div v-if="found && surat" class="px-5 py-5 space-y-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Jenis Surat</p>
                        <p class="mt-1 text-base font-bold text-slate-900">{{ surat.jenis_surat }}</p>
                        <p v-if="surat.kategori" class="text-xs text-slate-400">{{ surat.kategori }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">Nomor Surat</p>
                            <p class="mt-1 font-mono text-xs font-semibold text-slate-800 break-all">{{ surat.nomor_surat ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">Tanggal Terbit</p>
                            <p class="mt-1 text-xs font-semibold text-slate-800">{{ surat.tanggal_selesai ?? surat.tanggal ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">Nama</p>
                            <p class="mt-1 text-xs font-semibold text-slate-800">{{ surat.nama_pemohon ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">NIM</p>
                            <p class="mt-1 font-mono text-xs font-semibold text-slate-800">{{ surat.nim ?? '—' }}</p>
                        </div>
                        <div v-if="surat.program_studi" class="col-span-2 rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">Program Studi</p>
                            <p class="mt-1 text-xs font-semibold text-slate-800">{{ surat.program_studi }}</p>
                        </div>
                        <div v-if="surat.approved_by" class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">Disetujui oleh</p>
                            <p class="mt-1 text-xs font-semibold text-slate-800">{{ surat.approved_by }}</p>
                        </div>
                        <div v-if="surat.approved_at" class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] text-slate-400">Tanggal Persetujuan</p>
                            <p class="mt-1 text-xs font-semibold text-slate-800">{{ surat.approved_at }}</p>
                        </div>
                    </div>

                    <!-- Badge valid -->
                    <div v-if="state === 'valid'" class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <span class="text-emerald-600">✓</span>
                        <p class="text-xs font-medium text-emerald-800">Dokumen ini sah dan diterbitkan secara resmi oleh FMIKOM.</p>
                    </div>
                    <div v-if="state === 'revoked'" class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                        <span class="text-red-600">✗</span>
                        <p class="text-xs font-medium text-red-800">Dokumen ini telah dicabut dan tidak berlaku lagi.</p>
                    </div>
                    <div v-if="state === 'pending'" class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <span class="text-amber-600">⏳</span>
                        <p class="text-xs font-medium text-amber-800">Dokumen ini masih dalam proses persetujuan.</p>
                    </div>
                </div>

                <!-- Tidak ditemukan -->
                <div v-if="!found" class="px-5 py-6">
                    <p class="text-sm font-medium text-slate-700 mb-3">Kemungkinan penyebab:</p>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-xs text-slate-500"><span class="mt-0.5 text-slate-300">•</span>QR code rusak atau tidak terbaca sempurna</li>
                        <li class="flex items-start gap-2 text-xs text-slate-500"><span class="mt-0.5 text-slate-300">•</span>Surat belum mendapatkan persetujuan resmi</li>
                        <li class="flex items-start gap-2 text-xs text-slate-500"><span class="mt-0.5 text-slate-300">•</span>Surat tidak tercatat dalam sistem kami</li>
                    </ul>
                </div>

                <!-- Footer card -->
                <div class="border-t border-slate-100 bg-slate-50 px-5 py-3 text-center">
                    <p class="text-[10px] text-slate-400">
                        Dicek pada {{ new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center space-y-1">
                <p class="text-xs text-slate-400">Sistem Manajemen Surat FMIKOM</p>
                <a href="/verifikasi-qr" class="text-xs text-emerald-600 hover:underline">Verifikasi token lain</a>
            </div>
        </div>
    </div>
</template>
