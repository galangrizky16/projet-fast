<script setup lang="ts">
// File: resources/js/components/admin/DynamicField.vue
// Menggantikan: components/Fast/AdminSuratDynamicField.vue
// Tambahan: support field type 'recipient' (Kepada Yth)
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type FieldOption = { label: string; value: string };

export type DynamicField = {
    name: string;
    label: string;
    type: string;
    required?: boolean;
    placeholder?: string;
    help?: string;
    options?: FieldOption[];
    repeatable?: boolean;
    add_label?: string;
    item_label?: string;
};

const props = defineProps<{
    field: DynamicField;
    modelValue: string | boolean | string[] | Record<string, string>;
    error?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | boolean | string[] | Record<string, string>];
}>();

// ── Recipient (Kepada Yth) ───────────────────────────────────────────────

const RECIPIENT_PRESETS = [
    { label: 'Dekan FMIKOM', value: 'Dekan Fakultas Matematika dan Ilmu Komputer' },
    { label: 'Wakil Dekan', value: 'Wakil Dekan FMIKOM' },
    { label: 'Kaprodi Informatika', value: 'Ketua Program Studi Informatika' },
    { label: 'Kaprodi Sistem Informasi', value: 'Ketua Program Studi Sistem Informasi' },
    { label: 'Sekretaris Fakultas', value: 'Sekretaris Fakultas FMIKOM' },
    { label: 'Semua Dosen FMIKOM', value: 'Seluruh Dosen Fakultas Matematika dan Ilmu Komputer' },
    { label: 'Semua Mahasiswa', value: 'Seluruh Mahasiswa FMIKOM' },
    { label: 'Ketik manual', value: '__manual__' },
];

const recipientMode = ref<'preset' | 'manual'>('preset');
const selectedPreset = ref('');
const manualRecipient = ref('');

function onPresetChange(val: string) {
    if (val === '__manual__') {
        recipientMode.value = 'manual';
        selectedPreset.value = '';
        emit('update:modelValue', manualRecipient.value);
    } else {
        recipientMode.value = 'preset';
        manualRecipient.value = '';
        emit('update:modelValue', val);
    }
}

function onManualInput(val: string) {
    manualRecipient.value = val;
    emit('update:modelValue', val);
}

// ── Repeatable ────────────────────────────────────────────────────────────

const repeatableItems = computed<string[]>(() => {
    const val = props.modelValue;
    if (Array.isArray(val)) return val as string[];
    return [''];
});

function updateRepeatableItem(idx: number, val: string): void {
    const items = [...repeatableItems.value];
    items[idx] = val;
    emit('update:modelValue', items);
}

function addRepeatableItem(): void {
    emit('update:modelValue', [...repeatableItems.value, '']);
}

function removeRepeatableItem(idx: number): void {
    const items = repeatableItems.value.filter((_, i) => i !== idx);
    emit('update:modelValue', items.length ? items : ['']);
}

// ── Generic value ─────────────────────────────────────────────────────────

const stringValue = computed<string>({
    get: () => String(props.modelValue ?? ''),
    set: (val) => emit('update:modelValue', val),
});

const boolValue = computed<boolean>({
    get: () => Boolean(props.modelValue),
    set: (val) => emit('update:modelValue', val),
});

const multiValue = computed<string[]>({
    get: () => Array.isArray(props.modelValue) ? props.modelValue as string[] : [],
    set: (val) => emit('update:modelValue', val),
});

function toggleMulti(val: string): void {
    const current = multiValue.value;
    const next = current.includes(val)
        ? current.filter(v => v !== val)
        : [...current, val];
    emit('update:modelValue', next);
}

const inputClass = 'h-11 rounded-2xl border-slate-200 bg-white text-sm focus:border-indigo-400 focus:ring-indigo-100';
</script>

<template>
    <div class="space-y-1.5">
        <Label :for="field.name" class="text-sm font-medium text-slate-700">
            {{ field.label }}
            <span v-if="field.required" class="ml-0.5 text-rose-500">*</span>
        </Label>

        <!-- ── TYPE: recipient (Kepada Yth) ── -->
        <template v-if="field.type === 'recipient'">
            <select
                :value="recipientMode === 'preset' ? selectedPreset : '__manual__'"
                class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                @change="onPresetChange(($event.target as HTMLSelectElement).value); selectedPreset = ($event.target as HTMLSelectElement).value"
            >
                <option value="" disabled>-- Pilih penerima --</option>
                <option
                    v-for="preset in RECIPIENT_PRESETS"
                    :key="preset.value"
                    :value="preset.value"
                >
                    {{ preset.label }}
                </option>
            </select>

            <div v-if="recipientMode === 'manual'" class="mt-2">
                <Input
                    :id="field.name"
                    :value="manualRecipient"
                    type="text"
                    placeholder="Tulis nama / jabatan penerima..."
                    :class="inputClass"
                    @input="onManualInput(($event.target as HTMLInputElement).value)"
                />
            </div>

            <p v-if="modelValue && recipientMode === 'preset'" class="mt-1.5 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs text-indigo-700">
                Kepada Yth. {{ modelValue }}
            </p>
        </template>

        <!-- ── TYPE: textarea ── -->
        <textarea
            v-else-if="field.type === 'textarea'"
            :id="field.name"
            :value="stringValue"
            :placeholder="field.placeholder"
            :required="field.required"
            rows="4"
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
            @input="stringValue = ($event.target as HTMLTextAreaElement).value"
        />

        <!-- ── TYPE: select ── -->
        <select
            v-else-if="field.type === 'select'"
            :id="field.name"
            :value="stringValue"
            :required="field.required"
            class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
            @change="stringValue = ($event.target as HTMLSelectElement).value"
        >
            <option value="" disabled>{{ field.placeholder || '-- Pilih --' }}</option>
            <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>

        <!-- ── TYPE: checkbox ── -->
        <div v-else-if="field.type === 'checkbox'" class="flex items-center gap-2 pt-1">
            <input
                :id="field.name"
                type="checkbox"
                :checked="boolValue"
                class="rounded border-slate-300"
                @change="boolValue = ($event.target as HTMLInputElement).checked"
            />
            <label :for="field.name" class="text-sm text-slate-600">{{ field.placeholder || field.label }}</label>
        </div>

        <!-- ── TYPE: checkbox-group / multiselect ── -->
        <div v-else-if="['checkbox-group', 'multiselect'].includes(field.type)" class="space-y-2">
            <div v-for="opt in field.options" :key="opt.value" class="flex items-center gap-2">
                <input
                    :id="`${field.name}-${opt.value}`"
                    type="checkbox"
                    :value="opt.value"
                    :checked="multiValue.includes(opt.value)"
                    class="rounded border-slate-300"
                    @change="toggleMulti(opt.value)"
                />
                <label :for="`${field.name}-${opt.value}`" class="text-sm text-slate-700">{{ opt.label }}</label>
            </div>
        </div>

        <!-- ── TYPE: radio ── -->
        <div v-else-if="field.type === 'radio'" class="space-y-2">
            <div v-for="opt in field.options" :key="opt.value" class="flex items-center gap-2">
                <input
                    :id="`${field.name}-${opt.value}`"
                    type="radio"
                    :name="field.name"
                    :value="opt.value"
                    :checked="stringValue === opt.value"
                    class="border-slate-300 text-indigo-600"
                    @change="stringValue = opt.value"
                />
                <label :for="`${field.name}-${opt.value}`" class="text-sm text-slate-700">{{ opt.label }}</label>
            </div>
        </div>

        <!-- ── TYPE: repeatable ── -->
        <div v-else-if="field.type === 'repeatable'" class="space-y-2">
            <div v-for="(item, idx) in repeatableItems" :key="idx" class="flex items-center gap-2">
                <Input
                    :value="item"
                    :placeholder="field.placeholder || `${field.item_label || 'Item'} ${idx + 1}`"
                    :class="inputClass"
                    class="flex-1"
                    @input="updateRepeatableItem(idx, ($event.target as HTMLInputElement).value)"
                />
                <button
                    v-if="repeatableItems.length > 1"
                    type="button"
                    class="grid size-9 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-400 hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                    @click="removeRepeatableItem(idx)"
                >
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <button
                type="button"
                class="flex items-center gap-1.5 rounded-xl border border-dashed border-slate-300 px-4 py-2 text-xs font-medium text-slate-500 hover:border-indigo-300 hover:text-indigo-600"
                @click="addRepeatableItem"
            >
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ field.add_label || 'Tambah item' }}
            </button>
        </div>

        <!-- ── TYPE: default (text, number, date, email, url) ── -->
        <Input
            v-else
            :id="field.name"
            v-model="stringValue"
            :type="field.type"
            :placeholder="field.placeholder"
            :required="field.required"
            :step="field.type === 'number' ? '0.01' : undefined"
            :class="inputClass"
        />

        <!-- Help text -->
        <p v-if="field.help && !error" class="text-xs text-slate-400">{{ field.help }}</p>
        <!-- Error -->
        <p v-if="error" class="text-xs text-rose-600">{{ error }}</p>
    </div>
</template>
