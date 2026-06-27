<template>
    <ActionMenu
        aria-label="More options"
        align="right"
        title="More options"
        trigger-class="resource-detail-action"
    >
        <template #trigger>
            <IconEllipsisHorizontal class="resource-detail-action__icon" />
        </template>

        <template #default="{ close }">
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
        </template>
    </ActionMenu>
</template>

<script setup>
import ActionMenu from '@/components/ui/ActionMenu.vue';
import IconBookmark from '@/components/icons/IconBookmark.vue';
import IconCheck from '@/components/icons/IconCheck.vue';
import IconEllipsisHorizontal from '@/components/icons/IconEllipsisHorizontal.vue';
import IconExclamationTriangle from '@/components/icons/IconExclamationTriangle.vue';
import { useResourceActions } from '@/composables/useResourceActions';

const props = defineProps({
    resource: { type: Object, required: true },
});

const actions = useResourceActions(() => props.resource);
</script>
