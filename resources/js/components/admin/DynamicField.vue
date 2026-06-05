<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Plus, Trash2 } from 'lucide-vue-next';

type FieldOption = {
    label: string;
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
    repeatable?: boolean;
    add_label?: string;
    item_label?: string;
};

const props = defineProps<{
    field: DynamicField;
    modelValue: string | boolean | string[] | null | undefined;
    error?: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | boolean | string[]): void;
}>();

const repeatableItems = computed(() => {
    if (Array.isArray(props.modelValue) && props.modelValue.length > 0) {
        return props.modelValue.map((item) => String(item ?? ''));
    }

    return [''];
});

function updateValue(value: string | boolean | string[]) {
    emit('update:modelValue', value);
}

function updateRepeatableItem(index: number, value: string) {
    const items = [...repeatableItems.value];
    items[index] = value;
    updateValue(items);
}

function addRepeatableItem() {
    updateValue([...repeatableItems.value, '']);
}

function removeRepeatableItem(index: number) {
    const items = repeatableItems.value.filter((_, itemIndex) => itemIndex !== index);
    updateValue(items.length > 0 ? items : ['']);
}

function inputType(type: string) {
    return ['date', 'email', 'number'].includes(type) ? type : 'text';
}
</script>

<template>
    <div class="space-y-3 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="space-y-1">
            <Label :for="field.name" class="text-sm font-semibold text-slate-800">
                {{ field.label }}
                <span v-if="field.required" class="text-rose-500">*</span>
            </Label>
            <p v-if="field.help" class="text-xs leading-5 text-slate-500">
                {{ field.help }}
            </p>
        </div>

        <template v-if="field.repeatable || field.type === 'repeatable'">
            <div class="space-y-3">
                <div
                    v-for="(item, index) in repeatableItems"
                    :key="`${field.name}-${index}`"
                    class="flex items-center gap-3"
                >
                    <Input
                        :id="index === 0 ? field.name : `${field.name}-${index}`"
                        :model-value="item"
                        :placeholder="field.placeholder || `${field.item_label || 'Item'} ${index + 1}`"
                        class="h-12 rounded-2xl border-slate-200"
                        @update:model-value="(value) => updateRepeatableItem(index, String(value ?? ''))"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        class="h-12 w-12 rounded-2xl border-slate-200 px-0"
                        @click="removeRepeatableItem(index)"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    class="rounded-2xl border-dashed border-slate-300 text-slate-700"
                    @click="addRepeatableItem"
                >
                    <Plus class="mr-2 size-4" />
                    {{ field.add_label || 'Tambah' }}
                </Button>
            </div>
        </template>

        <textarea
            v-else-if="field.type === 'textarea'"
            :id="field.name"
            :value="String(modelValue ?? '')"
            :placeholder="field.placeholder"
            class="min-h-28 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
            @input="updateValue(($event.target as HTMLTextAreaElement).value)"
        />

        <select
            v-else-if="field.type === 'select'"
            :id="field.name"
            :value="String(modelValue ?? '')"
            class="h-12 w-full rounded-2xl border border-slate-200 px-4 text-sm text-slate-800 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100"
            @change="updateValue(($event.target as HTMLSelectElement).value)"
        >
            <option value="">
                Pilih {{ field.label.toLowerCase() }}
            </option>
            <option
                v-for="option in field.options ?? []"
                :key="`${field.name}-${option.value}`"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <Input
            v-else
            :id="field.name"
            :model-value="String(modelValue ?? '')"
            :type="inputType(field.type)"
            :placeholder="field.placeholder"
            class="h-12 rounded-2xl border-slate-200"
            @update:model-value="(value) => updateValue(String(value ?? ''))"
        />

        <p v-if="error" class="text-sm text-rose-600">
            {{ error }}
        </p>
    </div>
</template>
