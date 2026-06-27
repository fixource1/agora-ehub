<template>
    <RouterLink :to="to" custom v-slot="{ navigate }">
        <button
            type="button"
            draggable="false"
            class="app-bottom-nav-item flex flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2 text-[10px] font-medium sm:text-[11px]"
            :class="active ? 'app-bottom-nav-item--active text-brand' : 'text-muted'"
            @click="navigate"
        >
            <component
                :is="icon"
                :filled="active"
                class="h-5 w-5 shrink-0 sm:h-[1.375rem] sm:w-[1.375rem]"
            />
            <span class="leading-none">{{ label }}</span>
        </button>
    </RouterLink>
</template>

<script setup>
defineProps({
    to: { type: String, required: true },
    label: { type: String, required: true },
    icon: { type: Object, required: true },
    active: { type: Boolean, default: false },
});
</script>
