<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    roles?: { slug: string; nama: string }[];
}>();

const showPassword = ref(false);

const form = useForm({
    email:    '',
    password: '',
    role:     '',
    remember: false,
});

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}

const roleOptions = computed(() => props.roles ?? []);

// ── Auto-fill akun demo saat role dipilih ──────────────────────────────────
const accountMap: Record<string, { email: string; password: string }> = {
    admin:     { email: 'tiffanyhellen27@gmail.com',    password: 'admin1234' },
    mahasiswa: { email: 'hellentiffanyyy@gmail.com',    password: 'fanny1234' },
    dosen:     { email: 'hellenfast1@gmail.com',          password: 'laora1234' },
    kaprodi:   { email: 'hellenfast2@gmail.com',          password: 'anna1234' },
    dekan:     { email: 'hellenfast3@gmail.com',          password: 'moana1234' },
};

watch(() => form.role, (slug) => {
    const acc = slug ? accountMap[slug] : undefined;
    if (acc) {
        form.email    = acc.email;
        form.password = acc.password;
    }
});
</script>

<template>
    <Head title="Login — FAST" />

    <div
        class="min-h-screen flex items-stretch"
        style="font-family: 'Plus Jakarta Sans', sans-serif;"
    >
        <!-- Panel Kiri — Branding -->
        <div
            class="relative hidden w-[45%] lg:flex flex-col justify-between p-14 overflow-hidden"
            style="background: linear-gradient(145deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);"
        >
            <div
                class="absolute inset-0 opacity-[0.04]"
                style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;"
            />

            <div class="relative z-10 flex items-center gap-5">
                <img
                    src="/logo.png"
                    alt="Logo UNUGHA"
                    class="h-16 w-auto object-contain brightness-110"
                />
                <div>
                    <p class="text-[13px] font-semibold tracking-wide text-sky-300">SISTEM INFORMASI</p>
                    <p class="text-[11px] tracking-wider text-slate-400 mt-0.5">FMIKOM — UNUGHA Cilacap</p>
                </div>
            </div>

            <div class="relative z-10">
                <p class="text-[11px] font-semibold tracking-[0.2em] uppercase text-sky-400/60 mb-5">
                    Portal Akademik
                </p>
                <h1 class="text-[2.6rem] font-bold leading-[1.1] text-white tracking-tight">
                    FAST
                </h1>
                <p class="text-base font-medium text-slate-300 mt-3">
                    FMIKOM Administration System Technology
                </p>
                <p class="text-sm leading-relaxed text-slate-500 mt-5 max-w-[260px]">
                    Kelola surat akademik, verifikasi digital, dan proses administrasi fakultas dengan lebih cepat dan terstruktur.
                </p>

                <div class="mt-10 space-y-3">
                    <div
                        v-for="(item, i) in [
                            { text: 'Pengajuan surat digital' },
                            { text: 'Verifikasi QR otomatis' },
                            { text: 'Multi-level approval' },
                        ]"
                        :key="i"
                        class="flex items-center gap-3"
                    >
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-500/20">
                            <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                                <path d="M3 6L5 8L9 4" stroke="#7dd3fc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-[13px] text-slate-400">{{ item.text }}</p>
                    </div>
                </div>
            </div>

            <div class="relative z-10">
                <p class="text-[11px] text-slate-600">
                    Universitas Nahdlatul Ulama Al Ghazali Cilacap
                </p>
            </div>
        </div>

        <!-- Panel Kanan — Form -->
        <div class="relative flex flex-1 items-center justify-center bg-slate-50 px-6 py-12 lg:px-16 min-h-screen lg:min-h-0">
            <div class="absolute top-0 left-0 right-0 h-48 bg-gradient-to-b from-sky-50 to-transparent" />

            <div class="relative z-10 w-full max-w-[400px]">
                <div class="mb-8 flex items-center gap-4 lg:hidden">
                    <img src="/logo.png" alt="Logo UNUGHA" class="h-12 w-auto object-contain" />
                    <div>
                        <p class="text-sm font-bold text-slate-900 leading-none">FAST</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">FMIKOM Administration System</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-8 py-9 shadow-[0_2px_16px_rgba(15,23,42,0.06)]">
                    <div class="mb-7">
                        <h2 class="text-xl font-bold text-slate-900">Selamat Datang</h2>
                        <p class="text-sm text-slate-500 mt-1.5">Masuk dengan akun FMIKOM Anda</p>
                    </div>

                    <div
                        v-if="status"
                        class="mb-5 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-[13px] font-medium text-sky-700"
                    >
                        {{ status }}
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="space-y-1.5">
                            <label for="role" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                Masuk sebagai
                            </label>
                            <select
                                id="role"
                                v-model="form.role"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 text-[13px] text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-[3px] focus:ring-sky-100 cursor-pointer"
                                :class="form.errors.role ? 'border-red-300' : ''"
                            >
                                <option value="" disabled>— Pilih role —</option>
                                <option v-for="r in roleOptions" :key="r.slug" :value="r.slug">{{ r.nama }}</option>
                            </select>
                            <InputError :message="form.errors.role" class="text-[11px] text-red-500" />
                        </div>

                        <div class="space-y-1.5">
                            <label for="email" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                Email
                            </label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
                                    width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="nama@unugha.ac.id"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 pl-10 text-[13px] text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-[3px] focus:ring-sky-100"
                                    :class="form.errors.email ? 'border-red-300' : ''"
                                />
                            </div>
                            <InputError :message="form.errors.email" class="text-[11px] text-red-500" />
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                    Password
                                </label>
                                <a
                                    v-if="canResetPassword"
                                    href="/forgot-password"
                                    :tabindex="5"
                                    class="text-[11px] font-medium text-sky-600 transition hover:text-sky-700"
                                >
                                    Lupa password?
                                </a>
                            </div>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
                                    width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 pl-10 pr-10 text-[13px] text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-[3px] focus:ring-sky-100"
                                    :class="form.errors.password ? 'border-red-300' : ''"
                                />
                                <button
                                    type="button"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                                    @click="showPassword = !showPassword"
                                >
                                    <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                        <path d="M10.73 5.08A10.66 10.66 0 0 1 12 5c7 0 10 7 10 7a17.75 17.75 0 0 1-1.67 2.68" />
                                        <path d="M6.61 6.61A13.38 13.38 0 0 0 2 12s3 7 10 7c2.19 0 4.16-.53 5.87-1.43" />
                                        <line x1="2" x2="22" y1="2" y2="22" />
                                    </svg>
                                </button>
                            </div>
                            <InputError :message="form.errors.password" class="text-[11px] text-red-500" />
                        </div>

                        <div class="flex items-center gap-2.5">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                :tabindex="3"
                                class="size-4 cursor-pointer rounded border-slate-300 text-sky-600 focus:ring-sky-500/30"
                            />
                            <label for="remember" class="cursor-pointer select-none text-[13px] text-slate-600">
                                Ingat saya selama 30 hari
                            </label>
                        </div>

                        <button
                            type="submit"
                            :tabindex="4"
                            :disabled="form.processing"
                            class="mt-1 flex h-11 w-full items-center justify-center gap-2 rounded-xl text-sm font-bold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                            style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);"
                        >
                            <Spinner v-if="form.processing" class="size-4" />
                            <span v-if="!form.processing">Masuk ke Portal</span>
                            <span v-else>Memproses...</span>
                        </button>
                    </form>

                    <p class="mt-7 text-center text-[11px] text-slate-400 leading-relaxed">
                        &copy; {{ new Date().getFullYear() }} FAST — FMIKOM<br>UNUGHA Cilacap
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
