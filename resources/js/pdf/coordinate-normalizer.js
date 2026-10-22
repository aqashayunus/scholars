/**
 * Olympia Scholars - PDF Coordinate Normalizer
 * 
 * Provides mathematical mapping between screen pointer coordinates (CSS pixels),
 * canvas backing buffer pixels (scaled for Retina / HiDPI), and normalized
 * document coordinates (0.0 to 1.0 relative to original unscaled PDF page dimensions).
 */

export class CoordinateNormalizer {
    /**
     * Map pointer event clientX/clientY to normalized coordinates (0.0 - 1.0)
     * and unscaled PDF points (standard 72 DPI space).
     * 
     * @param {number} clientX
     * @param {number} clientY
     * @param {HTMLCanvasElement} canvas
     * @param {number} pdfOriginalWidth
     * @param {number} pdfOriginalHeight
     * @returns {{ normX: number, normY: number, pdfX: number, pdfY: number }}
     */
    static screenToPdf(clientX, clientY, canvas, pdfOriginalWidth, pdfOriginalHeight) {
        const rect = canvas.getBoundingClientRect();
        const cssX = clientX - rect.left;
        const cssY = clientY - rect.top;

        // Clamp to canvas bounds
        const clampedX = Math.max(0, Math.min(rect.width, cssX));
        const clampedY = Math.max(0, Math.min(rect.height, cssY));

        const normX = rect.width > 0 ? clampedX / rect.width : 0;
        const normY = rect.height > 0 ? clampedY / rect.height : 0;

        return {
            normX: Number(normX.toFixed(5)),
            normY: Number(normY.toFixed(5)),
            pdfX: Number((normX * pdfOriginalWidth).toFixed(2)),
            pdfY: Number((normY * pdfOriginalHeight).toFixed(2)),
        };
    }

    /**
     * Convert normalized coordinates (0.0 - 1.0) to current rendered canvas pixel coordinates.
     * 
     * @param {number} normX
     * @param {number} normY
     * @param {number} currentRenderedWidth (e.g. unscaledWidth * zoom)
     * @param {number} currentRenderedHeight (e.g. unscaledHeight * zoom)
     * @returns {{ x: number, y: number }}
     */
    static normToRendered(normX, normY, currentRenderedWidth, currentRenderedHeight) {
        return {
            x: normX * currentRenderedWidth,
            y: normY * currentRenderedHeight,
        };
    }

    /**
     * Calculate Euclidean distance between a point and a line segment.
     * Used for vector stroke segment proximity testing in the eraser.
     */
    static distToSegment(px, py, x1, y1, x2, y2) {
        const l2 = (x2 - x1) ** 2 + (y2 - y1) ** 2;
        if (l2 === 0) return Math.hypot(px - x1, py - y1);
        let t = ((px - x1) * (x2 - x1) + (py - y1) * (y2 - y1)) / l2;
        t = Math.max(0, Math.min(1, t));
        return Math.hypot(px - (x1 + t * (x2 - x1)), py - (y1 + t * (y2 - y1)));
    }
}
