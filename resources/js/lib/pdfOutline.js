import { PdfActionType } from '@embedpdf/models';

/**
 * @param {import('@embedpdf/models').PdfBookmarkObject} bookmark
 * @returns {number|null}
 */
export function resolveBookmarkPage(bookmark) {
    const target = bookmark?.target;

    if (! target) {
        return null;
    }

    if (target.type === 'destination' && target.destination) {
        return (target.destination.pageIndex ?? 0) + 1;
    }

    if (target.type === 'action' && target.action?.type === PdfActionType.Goto) {
        return (target.action.destination?.pageIndex ?? 0) + 1;
    }

    return null;
}

/**
 * @param {import('@embedpdf/models').PdfBookmarkObject[]} bookmarks
 * @param {number} depth
 * @returns {Array<{ id: string, title: string, page: number|null, depth: number }>}
 */
export function flattenPdfOutline(bookmarks, depth = 0) {
    const items = [];

    for (const [index, bookmark] of (bookmarks ?? []).entries()) {
        const page = resolveBookmarkPage(bookmark);
        const id = `${depth}-${index}-${bookmark.title}`;

        items.push({
            id,
            title: bookmark.title?.trim() || 'Untitled',
            page,
            depth,
        });

        if (bookmark.children?.length) {
            items.push(...flattenPdfOutline(bookmark.children, depth + 1));
        }
    }

    return items;
}
