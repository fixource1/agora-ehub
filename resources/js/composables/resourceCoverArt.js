export const COVER_PALETTES = {
    research: ['#132E54', '#1a2744'],
    'training-materials': ['#8D1436', '#6b1029'],
    education: ['#FFB61C', '#e5a419'],
    default: ['#00563F', '#044532'],
};

export function getCategoryPalette(categorySlug) {
    const slug = (categorySlug ?? 'default').toLowerCase();

    return COVER_PALETTES[slug] ?? COVER_PALETTES.default;
}

export function getCoverTypeLabel(typeSlug, fileType = null) {
    if (fileType) {
        const ext = fileType.toLowerCase();

        if (['mp4', 'mov', 'webm'].includes(ext)) {
            return 'VIDEO';
        }

        if (['mp3', 'wav', 'm4a'].includes(ext)) {
            return 'AUDIO';
        }

        if (ext === 'epub') {
            return 'EBOOK';
        }

        if (ext === 'pdf') {
            return 'PDF';
        }
    }

    return matchTypeSlug(typeSlug);
}

function matchTypeSlug(typeSlug) {
    if (typeSlug?.includes('video')) {
        return 'VIDEO';
    }

    if (typeSlug?.includes('audio')) {
        return 'AUDIO';
    }

    if (typeSlug?.includes('ebook')) {
        return 'EBOOK';
    }

    return 'PDF';
}

export function wrapCoverTitle(title, maxLines = 3) {
    const words = title.trim().split(/\s+/);
    const lines = [];
    let current = '';

    for (const word of words) {
        const candidate = current === '' ? word : `${current} ${word}`;

        if (candidate.length > 24 && current !== '') {
            lines.push(current);
            current = word;
        } else {
            current = candidate;
        }
    }

    if (current !== '') {
        lines.push(current);
    }

    return lines.slice(0, maxLines);
}
