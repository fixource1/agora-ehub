<template>
    <div ref="root" class="action-menu">
        <button
            type="button"
            class="action-menu__trigger"
            :class="triggerClass"
            :aria-label="ariaLabel"
            :aria-expanded="open"
            aria-haspopup="menu"
            @click.stop="toggle"
        >
            <slot name="trigger" />
        </button>

        <Teleport to="body">
            <Transition name="action-menu-backdrop">
                <button
                    v-if="open"
                    type="button"
                    class="action-menu__backdrop"
                    aria-label="Close menu"
                    @click="close"
                />
            </Transition>

            <Transition name="action-menu">
                <div
                    v-if="open"
                    ref="panel"
                    class="action-menu__panel"
                    :class="panelClasses"
                    role="menu"
                    @click.stop
                >
                    <p v-if="title" class="action-menu__title">{{ title }}</p>
                    <slot :close="close" />
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    ariaLabel: { type: String, default: 'Open menu' },
    triggerClass: { type: String, default: '' },
    align: { type: String, default: 'right' },
    title: { type: String, default: '' },
});

const open = ref(false);
const root = ref(null);
const panel = ref(null);
const panelPosition = ref({ top: 0, right: 0 });
let ignoreOutsideClick = false;

const panelClasses = computed(() => [
    props.align === 'right' ? 'action-menu__panel--right' : 'action-menu__panel--left',
    isMobileViewport() ? 'action-menu__panel--sheet' : 'action-menu__panel--anchored',
]);

function isMobileViewport() {
    return window.matchMedia('(max-width: 639px)').matches;
}

function updatePanelPosition() {
    if (! open.value || ! root.value || isMobileViewport()) {
        return;
    }

    const trigger = root.value.querySelector('.action-menu__trigger');
    const rect = trigger?.getBoundingClientRect();

    if (! rect) {
        return;
    }

    panelPosition.value = {
        top: rect.bottom + 8,
        right: Math.max(16, window.innerWidth - rect.right),
    };
}

function toggle() {
    open.value = ! open.value;

    if (open.value) {
        ignoreOutsideClick = true;
        nextTick(() => {
            updatePanelPosition();
            window.setTimeout(() => {
                ignoreOutsideClick = false;
            }, 0);
        });
    }
}

function close() {
    open.value = false;
}

function onDocumentClick(event) {
    if (ignoreOutsideClick || ! open.value) {
        return;
    }

    if (root.value?.contains(event.target) || panel.value?.contains(event.target)) {
        return;
    }

    close();
}

function onEscape(event) {
    if (event.key === 'Escape') {
        close();
    }
}

function onViewportChange() {
    if (open.value) {
        updatePanelPosition();
    }
}

watch(open, (isOpen) => {
    document.body.classList.toggle('action-menu-open', isOpen);
});

onMounted(() => {
    document.addEventListener('click', onDocumentClick, true);
    document.addEventListener('keydown', onEscape);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick, true);
    document.removeEventListener('keydown', onEscape);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
    document.body.classList.remove('action-menu-open');
});

defineExpose({ close, open });
</script>

<style scoped>
.action-menu__panel--anchored {
    position: fixed;
    top: v-bind('`${panelPosition.top}px`');
    right: v-bind('`${panelPosition.right}px`');
}
</style>
