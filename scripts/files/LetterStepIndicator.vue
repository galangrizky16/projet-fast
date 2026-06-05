<script setup lang="ts">
// File: resources/js/components/admin/LetterStepIndicator.vue
// Menggantikan: components/Fast/AdminSuratStepIndicator.vue

const props = withDefaults(defineProps<{
    currentStep: 1 | 2 | 3;
}>(), {
    currentStep: 1,
});

const steps = [
    { num: 1, label: 'Pilih Jenis' },
    { num: 2, label: 'Isi Form' },
    { num: 3, label: 'Preview & Submit' },
] as const;
</script>

<template>
    <div class="flex items-center rounded-xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
        <template v-for="(step, i) in steps" :key="step.num">
            <div class="flex items-center gap-2">
                <div
                    class="grid size-6 shrink-0 place-items-center rounded-full text-xs font-bold"
                    :class="{
                        'bg-indigo-600 text-white': currentStep === step.num,
                        'bg-emerald-500 text-white': currentStep > step.num,
                        'bg-slate-100 text-slate-400': currentStep < step.num,
                    }"
                >
                    <svg v-if="currentStep > step.num" class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span v-else>{{ step.num }}</span>
                </div>
                <span
                    class="hidden text-xs font-medium sm:block"
                    :class="{
                        'text-indigo-700': currentStep === step.num,
                        'text-emerald-700': currentStep > step.num,
                        'text-slate-400': currentStep < step.num,
                    }"
                >
                    {{ step.label }}
                </span>
            </div>
            <div
                v-if="i < steps.length - 1"
                class="mx-3 h-px flex-1"
                :class="currentStep > step.num ? 'bg-emerald-300' : 'bg-slate-200'"
            />
        </template>
    </div>
</template>
