<template>
    <aside class="bg-surface border-app flex h-full flex-col border-r">
        <div class="border-app border-b px-4 py-4">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-app font-semibold">Contents</p>
                    <p class="text-muted text-xs">{{ pageCountLabel }}</p>
                </div>
                <button
                    v-if="closable"
                    type="button"
                    class="text-muted tap-feedback flex h-8 w-8 items-center justify-center rounded-full"
                    @click="$emit('close')"
                >
                    ✕
                </button>
            </div>

            <div
                class="reader-sidebar-tabs bg-surface-muted ring-app inline-flex w-full rounded-2xl p-1 ring-1"
                role="tablist"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    role="tab"
                    class="reader-sidebar-tab tap-feedback flex-1 rounded-xl px-2 py-2 text-xs font-medium sm:text-sm"
                    :class="activeTab === tab.id ? 'bg-brand text-white shadow-sm' : 'text-muted'"
                    :aria-selected="activeTab === tab.id"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2">
            <template v-if="activeTab === 'contents'">
                <section v-if="outlineItems.length" class="mb-4">
                    <p class="text-muted px-2 pb-2 text-[11px] font-semibold uppercase tracking-wide">
                        Table of contents
                    </p>
                    <button
                        v-for="item in outlineItems"
                        :key="item.id"
                        type="button"
                        class="reader-sidebar-item tap-feedback flex w-full items-center justify-between rounded-2xl px-3 py-3 text-left text-sm transition"
                        :class="isActivePage(item.page) ? 'bg-brand-subtle text-brand' : 'text-app'"
                        :style="{ paddingLeft: `${12 + item.depth * 14}px` }"
                        :disabled="! item.page"
                        @click="item.page && $emit('select', item.page)"
                    >
                        <span class="min-w-0 truncate pr-2">{{ item.title }}</span>
                        <span v-if="item.page" class="text-muted shrink-0">{{ item.page }}</span>
                    </button>
                </section>

                <section>
                    <p class="text-muted px-2 pb-2 text-[11px] font-semibold uppercase tracking-wide">
                        {{ outlineItems.length ? 'All pages' : 'Pages' }}
                    </p>
                    <div class="reader-page-preview-grid">
                        <button
                            v-for="page in pageItems"
                            :key="page"
                            type="button"
                            class="reader-page-preview-card tap-feedback"
                            :class="{ 'reader-page-preview-card--active': isActivePage(page) }"
                            @click="$emit('select', page)"
                        >
                            <ReaderPagePreview
                                :page="page"
                                :fetch-thumbnail="fetchThumbnail"
                                :ready="thumbnailsReady"
                                :active="isActivePage(page)"
                            />
                            <span class="reader-page-preview-card__label">Page {{ page }}</span>
                        </button>
                    </div>
                </section>
            </template>

            <template v-else-if="activeTab === 'bookmarks'">
                <p v-if="! bookmarkItems.length" class="text-muted px-3 py-6 text-center text-sm">
                    Bookmark a page from the reader toolbar to save your place.
                </p>
                <article
                    v-for="item in bookmarkItems"
                    :key="item.id"
                    class="reader-sidebar-card reader-sidebar-card--bookmark mb-2"
                >
                    <div class="flex items-center gap-2 p-2.5 sm:p-3">
                        <button
                            type="button"
                            class="tap-feedback min-w-0 flex-1 text-left"
                            @click="$emit('select', item.page)"
                        >
                            <p
                                class="truncate text-sm font-medium"
                                :class="isActivePage(item.page) ? 'text-brand' : 'text-app'"
                            >
                                {{ bookmarkDisplayLabel(item) }}
                            </p>
                            <p
                                v-if="bookmarkHasSubtitle(item)"
                                class="text-muted mt-0.5 truncate text-xs"
                            >
                                Page {{ item.page }}
                            </p>
                        </button>
                        <button
                            type="button"
                            class="reader-sidebar-card__delete text-muted hover:text-app tap-feedback shrink-0"
                            title="Remove bookmark"
                            @click="$emit('remove-bookmark', item.id)"
                        >
                            ✕
                        </button>
                    </div>
                </article>
            </template>

            <template v-else>
                <button
                    type="button"
                    class="reader-note-add tap-feedback mb-3 flex w-full items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium"
                    @click="$emit('add-note', currentPage)"
                >
                    <IconNotes class="h-4 w-4" />
                    Add note on page {{ currentPage }}
                </button>

                <p v-if="annotationsLoading" class="text-muted px-3 py-6 text-center text-sm">
                    Loading notes…
                </p>
                <template v-else>
                    <p v-if="! userNotes.length && ! annotationItems.length" class="text-muted px-3 py-6 text-center text-sm">
                        Add a note to capture your thoughts on this page.
                    </p>

                    <div v-if="userNotes.length" class="mb-4 space-y-2">
                        <p class="text-muted px-2 text-[11px] font-semibold uppercase tracking-wide">Your notes</p>
                        <article
                            v-for="item in userNotes"
                            :key="item.id"
                            class="reader-sidebar-card reader-sidebar-card--note"
                        >
                            <div class="flex items-start gap-2 p-2.5 sm:p-3">
                                <button
                                    type="button"
                                    class="tap-feedback min-w-0 flex-1 text-left"
                                    @click="$emit('select', item.page)"
                                >
                                    <p class="text-muted text-xs">Page {{ item.page }}</p>
                                    <p
                                        class="mt-1 text-sm leading-relaxed"
                                        :class="isActivePage(item.page) ? 'text-brand' : 'text-app'"
                                    >
                                        {{ item.text }}
                                    </p>
                                </button>
                                <button
                                    type="button"
                                    class="reader-sidebar-card__delete text-muted hover:text-app tap-feedback shrink-0"
                                    title="Remove note"
                                    @click="$emit('remove-note', item.id)"
                                >
                                    ✕
                                </button>
                            </div>
                        </article>
                    </div>

                    <div v-if="annotationItems.length">
                        <p class="text-muted px-2 pb-2 text-[11px] font-semibold uppercase tracking-wide">Document highlights</p>
                        <button
                            v-for="item in annotationItems"
                            :key="item.id"
                            type="button"
                            class="reader-sidebar-item tap-feedback mb-1 flex w-full items-center justify-between rounded-2xl px-3 py-3 text-left text-sm transition"
                            :class="isActivePage(item.page) ? 'bg-brand-subtle text-brand' : 'text-app'"
                            @click="$emit('select', item.page)"
                        >
                            <span class="min-w-0 truncate pr-2">{{ item.title }}</span>
                            <span class="text-muted shrink-0">{{ item.page }}</span>
                        </button>
                    </div>
                </template>
            </template>
        </div>
    </aside>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import IconNotes from '@/components/icons/IconNotes.vue';
import ReaderPagePreview from '@/components/reader/ReaderPagePreview.vue';

const props = defineProps({
    outlineItems: { type: Array, default: () => [] },
    bookmarkItems: { type: Array, default: () => [] },
    userNotes: { type: Array, default: () => [] },
    annotationItems: { type: Array, default: () => [] },
    annotationsLoading: { type: Boolean, default: false },
    currentPage: { type: Number, default: 1 },
    totalPages: { type: Number, default: 1 },
    fetchThumbnail: { type: Function, default: null },
    thumbnailsReady: { type: Boolean, default: false },
    closable: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'select', 'remove-bookmark', 'remove-note', 'add-note', 'request-annotations']);

const activeTab = ref('contents');

const tabs = [
    { id: 'contents', label: 'Contents' },
    { id: 'bookmarks', label: 'Bookmarks' },
    { id: 'annotations', label: 'Notes' },
];

const pageItems = computed(() => {
    const total = Math.max(1, Number(props.totalPages) || 1);

    return Array.from({ length: total }, (_, index) => index + 1);
});

const pageCountLabel = computed(() => {
    const total = Math.max(1, Number(props.totalPages) || 1);

    return total === 1 ? '1 page' : `${total} pages`;
});

watch(activeTab, (tab) => {
    if (tab === 'annotations') {
        emit('request-annotations');
    }
});

function isActivePage(page) {
    return Number(page) === Number(props.currentPage);
}

function isDefaultBookmarkLabel(item) {
    const label = String(item.label ?? '').trim();

    return label === `Page ${item.page}` || label === String(item.page);
}

function bookmarkDisplayLabel(item) {
    if (isDefaultBookmarkLabel(item)) {
        return `Page ${item.page}`;
    }

    return item.label;
}

function bookmarkHasSubtitle(item) {
    return ! isDefaultBookmarkLabel(item);
}
</script>
