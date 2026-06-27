import { computed, toValue } from 'vue';
import {
    buildReportMailto,
    canUseNativeShare,
    copyResourceCitation,
    copyResourceLink,
    shareResource,
} from '@/composables/useResourceShare';
import { useLibrary } from '@/composables/useLibrary';
import { useToast } from '@/composables/useToast';

export function useResourceActions(getResource) {
    const library = useLibrary();
    const { showToast } = useToast();
    const canNativeShare = canUseNativeShare();

    const collections = computed(() => library.state.collections);

    const isBookmarked = computed(() => {
        const slug = toValue(getResource)?.slug;

        return slug ? library.isBookmarked(slug) : false;
    });

    function isInCollection(collectionId) {
        const slug = toValue(getResource)?.slug;

        return slug ? library.isInCollection(collectionId, slug) : false;
    }

    async function copyLink(onComplete) {
        const resource = toValue(getResource);

        if (! resource?.slug) {
            return;
        }

        try {
            await copyResourceLink(resource.slug);
            showToast('Link copied to clipboard');
        } catch {
            showToast('Could not copy link');
        }

        onComplete?.();
    }

    async function share(onComplete) {
        const resource = toValue(getResource);

        if (! resource?.slug) {
            return;
        }

        try {
            const result = await shareResource(resource);
            showToast(result === 'shared' ? 'Shared successfully' : 'Link copied to clipboard');
        } catch (error) {
            if (error?.name !== 'AbortError') {
                showToast('Could not share resource');
            }
        }

        onComplete?.();
    }

    async function copyCitation(onComplete) {
        const resource = toValue(getResource);

        if (! resource?.slug) {
            return;
        }

        try {
            await copyResourceCitation(resource);
            showToast('Citation copied to clipboard');
        } catch {
            showToast('Could not copy citation');
        }

        onComplete?.();
    }

    function toggleBookmark(onComplete) {
        const resource = toValue(getResource);

        if (! resource?.slug) {
            return;
        }

        const added = library.toggleBookmark(resource.slug);
        showToast(added ? 'Bookmark added' : 'Bookmark removed');
        onComplete?.();
    }

    function toggleCollection(collectionId, onComplete) {
        const resource = toValue(getResource);

        if (! resource?.slug) {
            return;
        }

        const added = library.toggleCollectionMembership(collectionId, resource.slug);
        const collection = collections.value.find((item) => item.id === collectionId);
        showToast(
            added
                ? `Added to ${collection?.name ?? 'collection'}`
                : `Removed from ${collection?.name ?? 'collection'}`,
        );
        onComplete?.();
    }

    function reportIssue(onComplete) {
        const resource = toValue(getResource);

        if (! resource?.slug) {
            return;
        }

        onComplete?.();

        window.requestAnimationFrame(() => {
            window.location.href = buildReportMailto(resource);
        });
    }

    return {
        canNativeShare,
        collections,
        isBookmarked,
        isInCollection,
        copyLink,
        share,
        copyCitation,
        toggleBookmark,
        toggleCollection,
        reportIssue,
    };
}
