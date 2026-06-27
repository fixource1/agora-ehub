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

const FILE_TYPE_ICONS = {
    pdf: IconDocument,
    epub: IconBooks,
    mp4: IconVideo,
    mov: IconVideo,
    webm: IconVideo,
    mp3: IconAudio,
    wav: IconAudio,
    m4a: IconAudio,
    ppt: IconPresentation,
    pptx: IconPresentation,
};

export function getResourceTypeIcon(typeSlug, fileType = null) {
    if (fileType) {
        const icon = FILE_TYPE_ICONS[fileType.toLowerCase()];

        if (icon) {
            return icon;
        }
    }

    return RESOURCE_TYPE_ICONS[typeSlug] ?? IconArchive;
}
