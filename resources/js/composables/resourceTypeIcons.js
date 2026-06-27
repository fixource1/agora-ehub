import IconAcademicCap from '@/components/icons/IconAcademicCap.vue';
import IconArchive from '@/components/icons/IconArchive.vue';
import IconAudio from '@/components/icons/IconAudio.vue';
import IconBooks from '@/components/icons/IconBooks.vue';
import IconDocument from '@/components/icons/IconDocument.vue';
import IconNewspaper from '@/components/icons/IconNewspaper.vue';
import IconPresentation from '@/components/icons/IconPresentation.vue';
import IconVideo from '@/components/icons/IconVideo.vue';

const RESOURCE_TYPE_ICONS = {
    ebook: IconBooks,
    'pdf-document': IconDocument,
    'research-paper': IconAcademicCap,
    journal: IconNewspaper,
    video: IconVideo,
    audio: IconAudio,
    presentation: IconPresentation,
    other: IconArchive,
};

export function getResourceTypeIcon(slug) {
    return RESOURCE_TYPE_ICONS[slug] ?? IconArchive;
}
