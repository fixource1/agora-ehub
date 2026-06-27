export function getResourceUrl(slug) {
    return `${window.location.origin}/resources/${slug}`;
}

export function buildResourceCitation(resource) {
    const author = resource?.authors?.[0]?.name ?? 'UPLB OVCRE';
    const year = resource?.metadata?.publication_date?.slice(0, 4) ?? 'n.d.';
    const title = resource?.title ?? 'Untitled resource';
    const url = getResourceUrl(resource?.slug ?? '');

    return `${author}. (${year}). ${title}. AGORA e-Hub. ${url}`;
}

export async function copyText(text) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);

            return;
        } catch {
            // Fall through to legacy copy for mobile / non-secure contexts.
        }
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    textarea.setSelectionRange(0, text.length);

    const copied = document.execCommand('copy');
    document.body.removeChild(textarea);

    if (! copied) {
        throw new Error('Copy failed');
    }
}

export async function copyResourceLink(slug) {
    await copyText(getResourceUrl(slug));
}

export async function copyResourceCitation(resource) {
    await copyText(buildResourceCitation(resource));
}

export async function shareResource(resource) {
    const url = getResourceUrl(resource.slug);
    const shareData = {
        title: resource.title,
        text: resource.subtitle ?? resource.description?.slice(0, 120) ?? resource.title,
        url,
    };

    if (navigator.share) {
        await navigator.share(shareData);

        return 'shared';
    }

    await copyText(url);

    return 'copied';
}

export function openResourceInNewTab(slug) {
    window.open(getResourceUrl(slug), '_blank', 'noopener,noreferrer');
}

export function buildReportMailto(resource) {
    const subject = encodeURIComponent(`AGORA e-Hub issue: ${resource.title}`);
    const body = encodeURIComponent(
        `Resource: ${resource.title}\n`
        + `URL: ${getResourceUrl(resource.slug)}\n\n`
        + 'Describe the issue:\n',
    );

    return `mailto:support@agora-ehub.local?subject=${subject}&body=${body}`;
}

export function canUseNativeShare() {
    return typeof navigator.share === 'function';
}
