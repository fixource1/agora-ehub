/**
 * @param {{ kind: 'buffer' | 'url', buffer?: ArrayBuffer, url?: string, requestOptions?: { headers?: Record<string, string> } }} source
 */
export async function loadPdfArrayBuffer(source) {
    if (source.kind === 'buffer') {
        if (! source.buffer?.byteLength) {
            throw new Error('The PDF file is empty.');
        }

        return source.buffer;
    }

    const response = await fetch(source.url, {
        headers: source.requestOptions?.headers ?? {},
    });

    if (! response.ok) {
        throw new Error('Could not download the PDF.');
    }

    const buffer = await response.arrayBuffer();

    if (! buffer.byteLength) {
        throw new Error('The PDF file is empty.');
    }

    return buffer;
}
