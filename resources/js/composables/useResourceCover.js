import { computed, ref, watch } from 'vue';
import { resolvePublicAssetUrl } from '@/config/api';

export function useResourceCover(resourceRef) {
    const coverImageFailed = ref(false);

    watch(
        () => resourceRef.value?.cover_image,
        () => {
            coverImageFailed.value = false;
        },
    );

    const coverImageUrl = computed(() => resolvePublicAssetUrl(resourceRef.value?.cover_image));

    const showCoverImage = computed(() => {
        if (coverImageFailed.value) {
            return false;
        }

        const cover = resourceRef.value?.cover_image;

        if (typeof cover === 'string' && cover.startsWith('blob:')) {
            return true;
        }

        return Boolean(coverImageUrl.value);
    });

    return {
        coverImageUrl,
        showCoverImage,
        coverImageFailed,
    };
}
