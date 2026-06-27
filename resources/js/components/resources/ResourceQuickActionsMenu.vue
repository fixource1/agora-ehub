<template>
    <Teleport to="body">
        <Transition name="action-menu-backdrop">
            <button
                v-if="isOpen"
                type="button"
                class="action-menu__backdrop"
                aria-label="Close quick actions"
                @click="close"
            />
        </Transition>

        <Transition name="action-menu">
            <div
                v-if="isOpen && resource"
                class="action-menu__panel action-menu__panel--sheet"
                role="menu"
                @click.stop
            >
                <p class="action-menu__title line-clamp-2">{{ resource.title }}</p>

                <button
                    v-if="actions.canNativeShare"
                    type="button"
                    class="action-menu__item"
                    role="menuitem"
                    @click="actions.share(close)"
                >
                    <IconShare class="action-menu__item-icon" />
                    Share…
                </button>
                <button
                    type="button"
                    class="action-menu__item"
                    role="menuitem"
                    @click="actions.copyLink(close)"
                >
                    <IconLink class="action-menu__item-icon" />
                    Copy link
                </button>
                <button
                    type="button"
                    class="action-menu__item"
                    role="menuitem"
                    @click="actions.copyCitation(close)"
                >
                    <IconDocument class="action-menu__item-icon" />
                    Copy citation
                </button>

                <button
                    type="button"
                    class="action-menu__item"
                    role="menuitem"
                    @click="actions.toggleBookmark(close)"
                >
                    <IconBookmark class="action-menu__item-icon" />
                    {{ actions.isBookmarked.value ? 'Remove bookmark' : 'Bookmark' }}
                </button>

                <div v-if="actions.collections.value.length" class="action-menu__section">
                    <p class="action-menu__section-label">Add to collection</p>
                    <button
                        v-for="collection in actions.collections.value"
                        :key="collection.id"
                        type="button"
                        class="action-menu__item action-menu__item--nested"
                        role="menuitem"
                        @click="actions.toggleCollection(collection.id, close)"
                    >
                        <span class="action-menu__item-icon action-menu__item-check">
                            <IconCheck v-if="actions.isInCollection(collection.id)" class="h-4 w-4" />
                        </span>
                        {{ collection.name }}
                    </button>
                </div>

                <button
                    type="button"
                    class="action-menu__item action-menu__item--danger"
                    role="menuitem"
                    @click="actions.reportIssue(close)"
                >
                    <IconExclamationTriangle class="action-menu__item-icon" />
                    Report issue
                </button>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onBeforeUnmount, onMounted } from 'vue';
import IconBookmark from '@/components/icons/IconBookmark.vue';
import IconCheck from '@/components/icons/IconCheck.vue';
import IconDocument from '@/components/icons/IconDocument.vue';
import IconExclamationTriangle from '@/components/icons/IconExclamationTriangle.vue';
import IconLink from '@/components/icons/IconLink.vue';
import IconShare from '@/components/icons/IconShare.vue';
import { useResourceActions } from '@/composables/useResourceActions';
import { useResourceQuickActions } from '@/composables/useResourceQuickActions';

const { isOpen, resource, closeQuickActions } = useResourceQuickActions();
const actions = useResourceActions(() => resource.value);

function close() {
    closeQuickActions();
}

function onEscape(event) {
    if (event.key === 'Escape' && isOpen.value) {
        close();
    }
}

onMounted(() => {
    document.addEventListener('keydown', onEscape);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onEscape);
    document.body.classList.remove('action-menu-open');
});
</script>
