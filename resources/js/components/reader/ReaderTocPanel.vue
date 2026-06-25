<template>
    <aside class="flex h-full flex-col border-r border-black/5 bg-white">
        <div class="flex items-center justify-between border-b px-4 py-4">
            <div>
                <p class="font-semibold">Contents</p>
                <p class="text-xs text-slate-500">{{ totalPages }} pages</p>
            </div>
            <button v-if="closable" class="text-slate-400 lg:hidden" @click="$emit('close')">✕</button>
        </div>
        <div class="flex-1 overflow-y-auto p-2">
            <button
                v-for="item in items"
                :key="item.title"
                class="flex w-full items-center justify-between rounded-lg px-3 py-3 text-left text-sm"
                :class="item.active ? 'border-l-4 border-primary-600 bg-primary-50 text-primary-700' : 'text-slate-700'"
            >
                <span>{{ item.title }}</span>
                <span class="text-slate-400">{{ item.page }}</span>
            </button>
        </div>
        <div class="border-t p-3">
            <button class="w-full rounded-xl bg-slate-50 py-2.5 text-sm font-medium text-slate-700">
                Bookmarks & Highlights
            </button>
        </div>
    </aside>
</template>

<script setup>
defineProps({
    items: { type: Array, required: true },
    totalPages: { type: Number, default: 320 },
    closable: { type: Boolean, default: false },
});

defineEmits(['close']);
</script>
