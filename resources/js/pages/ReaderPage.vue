<template>
    <div class="reader-shell fixed inset-0 z-50 flex flex-col lg:grid lg:grid-cols-[280px_minmax(0,1fr)_300px]" :class="themeClass">
        <!-- TOC: tablet persistent / mobile overlay -->
        <ReaderTocPanel
            class="hidden lg:flex"
            :items="tocItems"
            :total-pages="totalPages"
        />

        <Transition name="sheet">
            <div v-if="showToc" class="absolute inset-0 z-20 flex lg:hidden">
                <div class="flex-1 bg-black/30" @click="showToc = false" />
                <ReaderTocPanel
                    class="w-[min(88vw,320px)] shadow-xl"
                    :items="tocItems"
                    :total-pages="totalPages"
                    closable
                    @close="showToc = false"
                />
            </div>
        </Transition>

        <!-- Main reading column -->
        <div class="flex min-h-0 min-w-0 flex-col">
            <header class="safe-top flex shrink-0 items-center justify-between border-b border-black/5 px-4 py-3 lg:px-6">
                <button class="flex items-center gap-1 text-sm font-medium" @click="router.push(`/resources/${route.params.slug}`)">
                    <IconBack class="h-4 w-4" />
                    <span class="hidden sm:inline">Library</span>
                </button>
                <div class="min-w-0 flex-1 px-4 text-center lg:text-left">
                    <p class="truncate text-sm font-semibold lg:hidden">{{ resource?.title }}</p>
                    <p class="hidden truncate text-sm font-semibold lg:block">{{ resource?.title }}</p>
                    <p class="hidden truncate text-xs text-slate-500 lg:block">{{ primaryAuthor }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="hidden h-9 w-9 items-center justify-center rounded-lg bg-black/5 sm:flex">🔍</button>
                    <button class="hidden h-9 w-9 items-center justify-center rounded-lg bg-black/5 sm:flex">🔖</button>
                    <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-black/5 lg:hidden" @click="showToc = true">☰</button>
                    <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-black/5 lg:hidden" @click="showAppearance = true">Aa</button>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-8 lg:px-12" :style="readingStyle">
                <p class="text-center text-xs font-semibold uppercase tracking-widest text-primary-600">Chapter 1</p>
                <h1 class="mt-3 text-center font-serif text-2xl font-bold leading-tight lg:text-4xl">
                    Our Place in the Cosmos
                </h1>
                <div class="mx-auto mt-6 max-w-prose font-serif text-base leading-relaxed lg:text-lg" :style="{ fontFamily }">
                    <blockquote class="my-6 border-l-4 border-amber-300 bg-amber-100/80 px-4 py-3 italic text-slate-700">
                        "We are a way for the cosmos to know itself." — Carl Sagan
                    </blockquote>
                    <p class="mb-4">
                        The integrated EPUB/PDF reader will render full document content here.
                        On tablet, the table of contents and appearance panels stay visible beside the reading pane.
                    </p>
                    <p>
                        Font size, themes, and line spacing update live from the appearance panel.
                    </p>
                </div>
            </div>

            <footer class="safe-bottom shrink-0 border-t border-black/5 px-4 py-3 lg:px-6">
                <input v-model="currentPage" type="range" min="1" :max="totalPages" class="w-full accent-primary-600">
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ currentPage }} of {{ totalPages }}</span>
                    <span class="hidden sm:inline">4 pages left in chapter</span>
                </div>
            </footer>
        </div>

        <!-- Appearance: tablet persistent / mobile sheet -->
        <ReaderAppearancePanel
            v-model="theme"
            class="hidden lg:flex"
            :font-family="fontFamily"
            :font-size="fontSize"
            :line-spacing="lineSpacing"
            @update:font-family="fontFamily = $event"
            @update:font-size="fontSize = $event"
            @update:line-spacing="lineSpacing = $event"
        />

        <Transition name="sheet-up">
            <div v-if="showAppearance" class="absolute inset-x-0 bottom-0 z-20 lg:hidden">
                <ReaderAppearancePanel
                    v-model="theme"
                    class="rounded-t-3xl shadow-2xl"
                    :font-family="fontFamily"
                    :font-size="fontSize"
                    :line-spacing="lineSpacing"
                    closable
                    @update:font-family="fontFamily = $event"
                    @update:font-size="fontSize = $event"
                    @update:line-spacing="lineSpacing = $event"
                    @close="showAppearance = false"
                />
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import IconBack from '@/components/icons/IconBack.vue';
import ReaderTocPanel from '@/components/reader/ReaderTocPanel.vue';
import ReaderAppearancePanel from '@/components/reader/ReaderAppearancePanel.vue';

const route = useRoute();
const router = useRouter();

const resource = ref(null);
const showToc = ref(false);
const showAppearance = ref(false);
const theme = ref('sepia');
const fontFamily = ref('Georgia');
const fontSize = ref(18);
const lineSpacing = ref('normal');
const currentPage = ref(13);
const totalPages = ref(320);

const primaryAuthor = computed(() => resource.value?.authors?.[0]?.name ?? '');

const themeClass = computed(() => ({
    light: 'bg-white text-slate-900',
    sepia: 'bg-[#f4ecd8] text-slate-900',
    gray: 'bg-slate-100 text-slate-900',
    dark: 'bg-slate-900 text-slate-100',
}[theme.value]));

const lineHeightMap = { tight: 1.4, normal: 1.7, wide: 2 };

const readingStyle = computed(() => ({
    fontSize: `${fontSize.value}px`,
    lineHeight: lineHeightMap[lineSpacing.value],
}));

const tocItems = [
    { title: 'Cover', page: 1, active: false },
    { title: 'Title Page', page: 2, active: false },
    { title: '1 Our Place in the Cosmos', page: 13, active: true },
    { title: '2 The Scale of the Universe', page: 28, active: false },
];

onMounted(async () => {
    const response = await window.axios.get(`/resources/${route.params.slug}`);
    resource.value = response.data.data;
    totalPages.value = response.data.data?.metadata?.page_count ?? 320;
});
</script>

<style scoped>
.sheet-enter-active, .sheet-leave-active { transition: opacity 0.2s ease; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.sheet-up-enter-active, .sheet-up-leave-active { transition: transform 0.25s ease; }
.sheet-up-enter-from, .sheet-up-leave-to { transform: translateY(100%); }
</style>
