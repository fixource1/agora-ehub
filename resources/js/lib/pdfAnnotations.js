import { PdfAnnotationSubtype } from '@embedpdf/models';

const MARKUP_TYPES = new Set([
    PdfAnnotationSubtype.HIGHLIGHT,
    PdfAnnotationSubtype.UNDERLINE,
    PdfAnnotationSubtype.SQUIGGLY,
    PdfAnnotationSubtype.STRIKEOUT,
    PdfAnnotationSubtype.TEXT,
    PdfAnnotationSubtype.POPUP,
    PdfAnnotationSubtype.FREETEXT,
]);

const TYPE_LABELS = {
    [PdfAnnotationSubtype.HIGHLIGHT]: 'Highlight',
    [PdfAnnotationSubtype.UNDERLINE]: 'Underline',
    [PdfAnnotationSubtype.SQUIGGLY]: 'Squiggly',
    [PdfAnnotationSubtype.STRIKEOUT]: 'Strikeout',
    [PdfAnnotationSubtype.TEXT]: 'Note',
    [PdfAnnotationSubtype.POPUP]: 'Note',
    [PdfAnnotationSubtype.FREETEXT]: 'Text',
};

/**
 * @param {import('@embedpdf/models').PdfAnnotationObject} annotation
 */
function annotationLabel(annotation) {
    if (annotation.contents?.trim()) {
        return annotation.contents.trim();
    }

    if (annotation.subject?.trim()) {
        return annotation.subject.trim();
    }

    return TYPE_LABELS[annotation.type] ?? 'Annotation';
}

/**
 * @param {Record<number, import('@embedpdf/models').PdfAnnotationObject[]>} annotationsByPage
 * @returns {Array<{ id: string, title: string, page: number, type: string }>}
 */
export function flattenPdfAnnotations(annotationsByPage) {
    const items = [];

    for (const [pageIndex, annotations] of Object.entries(annotationsByPage ?? {})) {
        for (const annotation of annotations ?? []) {
            if (! MARKUP_TYPES.has(annotation.type)) {
                continue;
            }

            items.push({
                id: `${pageIndex}-${annotation.id}`,
                title: annotationLabel(annotation),
                page: Number(pageIndex) + 1,
                type: annotation.type,
            });
        }
    }

    return items.sort((a, b) => a.page - b.page || a.title.localeCompare(b.title));
}
