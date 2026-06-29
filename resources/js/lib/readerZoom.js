export const READER_MIN_ZOOM = 0.75;
export const READER_MAX_ZOOM = 2;

export function formatZoomPercent(scale) {
    const value = Number(scale);

    if (! Number.isFinite(value) || value <= 0) {
        return '100%';
    }

    return `${Math.round(value * 100)}%`;
}

export function canZoomIn(currentScale) {
    return Number(currentScale) < READER_MAX_ZOOM - 0.01;
}

export function canZoomOut(currentScale) {
    return Number(currentScale) > READER_MIN_ZOOM + 0.01;
}
