<template>
    <svg
        v-bind="$attrs"
        viewBox="0 0 24 24"
        :fill="filled ? 'currentColor' : 'none'"
        :stroke="filled ? 'none' : 'currentColor'"
        stroke-width="1.5"
    >
        <path
            v-if="filled"
            d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"
        />
        <path
            v-if="filled"
            d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.007-.875 1.755-1.902 1.704l-7.682-.448-7.682.448A1.75 1.75 0 0 1 2.25 19.794v-6.198c0-.027.003-.054.01-.08l8.16-8.159Z"
        />
        <path
            v-else
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
        />
    </svg>
</template>

<script setup>
defineProps({
    filled: { type: Boolean, default: false },
});
</script>
