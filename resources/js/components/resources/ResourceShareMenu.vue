<template>
    <ActionMenu
        aria-label="Share resource"
        align="right"
        title="Share"
        trigger-class="resource-detail-action"
    >
        <template #trigger>
            <IconShare class="resource-detail-action__icon" />
        </template>

        <template #default="{ close }">
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
        </template>
    </ActionMenu>
</template>

<script setup>
import ActionMenu from '@/components/ui/ActionMenu.vue';
import IconDocument from '@/components/icons/IconDocument.vue';
import IconLink from '@/components/icons/IconLink.vue';
import IconShare from '@/components/icons/IconShare.vue';
import { useResourceActions } from '@/composables/useResourceActions';

const props = defineProps({
    resource: { type: Object, required: true },
});

const actions = useResourceActions(() => props.resource);
</script>
