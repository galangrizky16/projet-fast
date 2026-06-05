<script setup lang="ts">
// resources/js/pages/qr/VerifyResult.vue
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    found:   boolean;
    valid:   boolean;
    revoked: boolean;
    message: string;
    surat: {
        nomor_surat?:    string | null;
        jenis_surat?:    string | null;
        kategori?:       string | null;
        status?:         string | null;
        nama_pemohon?:   string | null;
        nim?:            string | null;
        keperluan?:      string | null;
        tanggal_terbit?: string | null;
    } | null;
}>();

const state = computed(() => {
    if (!props.found) return 'not_found';
    if (props.revoked) return 'revoked';
    if (props.valid) return 'valid';
    return 'pending';
});

const cfg = computed(() => ({
    valid:     { icon: '✓', label: 'DOKUMEN VALID',           color: 'emerald' },
    revoked:   { icon: '✗', label: 'DOKUMEN DICABUT',         color: 'red' },
    pending:   { icon: '⏳', label: 'MENUNGGU PERSETUJUAN',   color: 'amber' },
    not_found: { icon: '?', label: 'TIDAK DITEMUKAN',         color: 'slate' },
}[state.value]));

function fmt(d?: string | null) {
    if (!d) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d));
}

const now = new Intl.DateTimeFormat('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
}).format(new Date());
</script>

<template>
    <Head title="Verifikasi Dokumen — FMIKOM" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-emerald-50/20 flex items-center justify-center px-4 py-6">
        <div class="w-full max-w-sm space-y-3">

            <!-- Header -->
            <div class="text-center space-y-1.5">
                <div class="mx-auto flex size-11 items-center justify-center rounded-xl bg-emerald-600 shadow-md shadow-emerald-200">
                    <svg class="size-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">Verifikasi Dokumen</p>
                    <p class="text-[10px] text-slate-400">FMIKOM — Universitas NU Al Ghazali Cilacap</p>
                </div>
            </div>

            <!-- Card utama -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1"
                :class="{
                    'ring-emerald-200': cfg.color === 'emerald',
                    'ring-red-200':     cfg.color === 'red',
                    'ring-amber-200':   cfg.color === 'amber',
                    'ring-slate-200':   cfg.color === 'slate',
                }">

                <!-- Banner -->
                <div class="flex items-center gap-3 px-4 py-3.5"
                    :class="{
                        'bg-gradient-to-r from-emerald-500 to-teal-500': cfg.color === 'emerald',
                        'bg-gradient-to-r from-red-500 to-rose-500':     cfg.color === 'red',
                        'bg-gradient-to-r from-amber-400 to-orange-400': cfg.color === 'amber',
                        'bg-gradient-to-r from-slate-400 to-slate-500':  cfg.color === 'slate',
                    }">
                    <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-white/20 text-base font-bold text-white">
                        {{ cfg.icon }}
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/70">Status</p>
                        <p class="text-sm font-bold text-white leading-tight">{{ cfg.label }}</p>
                        <p class="text-[10px] text-white/80">{{ message }}</p>
                    </div>
                </div>

                <!-- Data surat -->
                <div v-if="found && surat" class="px-4 py-4 space-y-3">

                    <!-- Jenis surat -->
                    <div class="rounded-xl px-3 py-2.5"
                        :class="{
                            'bg-emerald-50 border border-emerald-100': cfg.color === 'emerald',
                            'bg-red-50 border border-red-100':         cfg.color === 'red',
                            'bg-amber-50 border border-amber-100':     cfg.color === 'amber',
                            'bg-slate-50 border border-slate-100':     cfg.color === 'slate',
                        }">
                        <p class="text-[10px] text-slate-400">Jenis Surat</p>
                        <p class="text-sm font-bold text-slate-900">{{ surat.jenis_surat ?? '-' }}</p>
                        <p v-if="surat.kategori" class="text-[10px] text-slate-400">{{ surat.kategori }}</p>
                    </div>

                    <!-- Grid info -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Nomor Surat</p>
                            <p class="mt-0.5 font-mono text-[10px] font-bold text-slate-800 break-all leading-4">{{ surat.nomor_surat ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Tanggal Terbit</p>
                            <p class="mt-0.5 text-[10px] font-bold text-slate-800">{{ fmt(surat.tanggal_terbit) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Nama</p>
                            <p class="mt-0.5 text-[10px] font-bold text-slate-800">{{ surat.nama_pemohon ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">NIM / NIP</p>
                            <p class="mt-0.5 font-mono text-[10px] font-bold text-slate-800">{{ surat.nim ?? '—' }}</p>
                        </div>
                        <div v-if="surat.program_studi" class="col-span-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Program Studi</p>
                            <p class="mt-0.5 text-[10px] font-bold text-slate-800">{{ surat.program_studi }}</p>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div v-if="state === 'valid'"
                        class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5">
                        <div class="grid size-6 shrink-0 place-items-center rounded-lg bg-emerald-500 text-white text-xs font-bold">✓</div>
                        <p class="text-xs font-medium text-emerald-800">Dokumen sah & diterbitkan resmi oleh FMIKOM.</p>
                    </div>
                    <div v-if="state === 'revoked'"
                        class="flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5">
                        <div class="grid size-6 shrink-0 place-items-center rounded-lg bg-red-500 text-white text-xs font-bold">✗</div>
                        <p class="text-xs font-medium text-red-800">Dokumen telah dicabut dan tidak berlaku lagi.</p>
                    </div>
                    <div v-if="state === 'pending'"
                        class="flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5">
                        <div class="grid size-6 shrink-0 place-items-center rounded-lg bg-amber-400 text-white text-xs">⏳</div>
                        <p class="text-xs font-medium text-amber-800">Dokumen masih dalam proses persetujuan.</p>
                    </div>
                </div>

                <!-- Tidak ditemukan -->
                <div v-if="!found" class="px-4 py-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 space-y-1.5">
                        <p class="text-xs font-semibold text-slate-700">Kemungkinan penyebab:</p>
                        <p class="text-[10px] text-slate-500">• QR code rusak atau tidak terbaca sempurna</p>
                        <p class="text-[10px] text-slate-500">• Surat belum mendapat persetujuan resmi</p>
                        <p class="text-[10px] text-slate-500">• Surat tidak tercatat dalam sistem</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-slate-100 bg-slate-50/80 px-4 py-2.5 flex items-center justify-between">
                    <p class="text-[9px] text-slate-400">{{ now }}</p>
                    <div class="flex items-center gap-1">
                        <div class="size-1.5 rounded-full animate-pulse"
                            :class="{
                                'bg-emerald-400': cfg.color === 'emerald',
                                'bg-red-400':     cfg.color === 'red',
                                'bg-amber-400':   cfg.color === 'amber',
                                'bg-slate-400':   cfg.color === 'slate',
                            }" />
                        <p class="text-[9px] font-medium text-slate-400">Real-time</p>
                    </div>
                </div>
            </div>

            <!-- Link kembali -->
            <div class="text-center">
                <a href="/verifikasi-qr"
                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:underline transition-colors">
                    ← Verifikasi token lain
                </a>
            </div>

        </div>
    </div>
</template>
