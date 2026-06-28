<template>
    <div
        class="pull-to-refresh"
        :class="{
            'pull-to-refresh--active': pullDistance > 0,
            'pull-to-refresh--refreshing': isRefreshing,
        }"
    >
        <div
            class="pull-to-refresh__indicator"
            :style="indicatorStyle"
            aria-hidden="true"
        >
            <IconLoading
                v-if="isRefreshing"
                class="pull-to-refresh__spinner h-5 w-5"
            />
            <span v-else class="pull-to-refresh__label">
                {{ pullDistance >= threshold ? 'Release to refresh' : 'Pull to refresh' }}
            </span>
        </div>

        <div
            ref="scrollContainer"
            class="pull-to-refresh__scroll"
            :class="scrollClass"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import IconLoading from '@/components/icons/IconLoading.vue';
import { usePullToRefresh } from '@/composables/usePullToRefresh';

const props = defineProps({
    disabled: { type: Boolean, default: false },
    scrollClass: { type: [String, Array, Object], default: '' },
    onRefresh: { type: Function, required: true },
});

const scrollContainer = ref(null);

const {
    pullDistance,
    isRefreshing,
    threshold,
    bindScrollElement,
} = usePullToRefresh({
    disabled: () => props.disabled,
    onRefresh: () => props.onRefresh(),
});

const indicatorStyle = computed(() => {
    const height = Math.max(0, pullDistance.value);

    return {
        height: `${height}px`,
        opacity: height > 0 || isRefreshing.value ? 1 : 0,
    };
});

onMounted(() => {
    if (scrollContainer.value) {
        bindScrollElement(scrollContainer.value);
    }
});
</script>
