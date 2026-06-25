<template>
    <aside class="flex h-full flex-col border-l border-black/5 bg-white p-5">
        <div class="mb-4 flex items-center justify-between">
            <p class="font-semibold">Appearance</p>
            <button v-if="closable" class="text-slate-400 lg:hidden" @click="$emit('close')">✕</button>
        </div>

        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Theme</p>
        <div class="mb-5 flex gap-3">
            <button
                v-for="t in themes"
                :key="t.id"
                class="h-10 w-10 rounded-full ring-2 ring-offset-2"
                :class="[t.class, modelValue === t.id ? 'ring-primary-600' : 'ring-transparent']"
                @click="$emit('update:modelValue', t.id)"
            />
        </div>

        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Font</p>
        <select
            :value="fontFamily"
            class="mb-5 w-full rounded-xl border-0 bg-slate-50 px-3 py-2.5 text-sm ring-1 ring-slate-200"
            @change="$emit('update:fontFamily', $event.target.value)"
        >
            <option value="Georgia">Georgia</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Inter">Inter</option>
        </select>

        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">Font size</p>
        <input
            :value="fontSize"
            type="range"
            min="16"
            max="48"
            class="w-full accent-primary-600"
            @input="$emit('update:fontSize', Number($event.target.value))"
        >
        <p class="mt-1 text-center text-sm text-slate-500">{{ fontSize }}px</p>

        <p class="mb-2 mt-5 text-xs font-medium uppercase tracking-wide text-slate-400">Line spacing</p>
        <div class="flex gap-2">
            <button
                v-for="opt in ['tight', 'normal', 'wide']"
                :key="opt"
                class="flex-1 rounded-lg py-2 text-xs capitalize ring-1"
                :class="lineSpacing === opt ? 'bg-primary-50 text-primary-700 ring-primary-200' : 'bg-white ring-slate-200'"
                @click="$emit('update:lineSpacing', opt)"
            >
                {{ opt }}
            </button>
        </div>
    </aside>
</template>

<script setup>
defineProps({
    modelValue: { type: String, default: 'sepia' },
    fontFamily: { type: String, default: 'Georgia' },
    fontSize: { type: Number, default: 18 },
    lineSpacing: { type: String, default: 'normal' },
    closable: { type: Boolean, default: false },
});

defineEmits(['update:modelValue', 'update:fontFamily', 'update:fontSize', 'update:lineSpacing', 'close']);

const themes = [
    { id: 'light', class: 'bg-white ring-1 ring-slate-200' },
    { id: 'sepia', class: 'bg-[#f4ecd8]' },
    { id: 'gray', class: 'bg-slate-200' },
    { id: 'dark', class: 'bg-slate-900' },
];
</script>
