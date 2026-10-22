/**
 * Olympia Scholars - Vector Stroke Eraser Engine
 * 
 * Provides vector-level segment collision detection for erasing strokes.
 */

import { CoordinateNormalizer } from './coordinate-normalizer.js';

export class EraserEngine {
    constructor(radiusPixels = 14) {
        this.radiusPixels = radiusPixels;
    }

    /**
     * Test and remove any strokes that intersect the eraser point.
     * 
     * @param {number} normX Eraser normalized X
     * @param {number} normY Eraser normalized Y
     * @param {number} renderedWidth Current canvas rendered width in CSS pixels
     * @param {number} renderedHeight Current canvas rendered height in CSS pixels
     * @param {Array} strokes Current page strokes array
     * @returns {Array} List of strokes that were erased
     */
    eraseAt(normX, normY, renderedWidth, renderedHeight, strokes) {
        if (!strokes || strokes.length === 0) return [];

        const radiusNormX = this.radiusPixels / renderedWidth;
        const radiusNormY = this.radiusPixels / renderedHeight;
        const erased = [];

        for (let i = strokes.length - 1; i >= 0; i--) {
            const stroke = strokes[i];
            if (!stroke.points || stroke.points.length === 0) continue;

            // Bounding box early rejection check
            if (this._outsideBoundingBox(normX, normY, stroke, radiusNormX, radiusNormY)) {
                continue;
            }

            // Proximity check on stroke points/segments
            if (this._intersectsStroke(normX, normY, stroke, renderedWidth, renderedHeight)) {
                const removed = strokes.splice(i, 1)[0];
                erased.push({ stroke: removed, index: i });
            }
        }

        return erased;
    }

    _outsideBoundingBox(normX, normY, stroke, radiusNormX, radiusNormY) {
        if (!stroke.bbox) {
            // Compute bounding box if not cached
            let minX = 1, minY = 1, maxX = 0, maxY = 0;
            for (const pt of stroke.points) {
                if (pt[0] < minX) minX = pt[0];
                if (pt[0] > maxX) maxX = pt[0];
                if (pt[1] < minY) minY = pt[1];
                if (pt[1] > maxY) maxY = pt[1];
            }
            stroke.bbox = { minX, minY, maxX, maxY };
        }

        const b = stroke.bbox;
        return (
            normX < b.minX - radiusNormX ||
            normX > b.maxX + radiusNormX ||
            normY < b.minY - radiusNormY ||
            normY > b.maxY + radiusNormY
        );
    }

    _intersectsStroke(normX, normY, stroke, renderedWidth, renderedHeight) {
        const px = normX * renderedWidth;
        const py = normY * renderedHeight;
        const radius = this.radiusPixels;
        const pts = stroke.points;

        if (pts.length === 1) {
            const x1 = pts[0][0] * renderedWidth;
            const y1 = pts[0][1] * renderedHeight;
            return Math.hypot(px - x1, py - y1) <= radius;
        }

        for (let j = 0; j < pts.length - 1; j++) {
            const x1 = pts[j][0] * renderedWidth;
            const y1 = pts[j][1] * renderedHeight;
            const x2 = pts[j + 1][0] * renderedWidth;
            const y2 = pts[j + 1][1] * renderedHeight;

            const dist = CoordinateNormalizer.distToSegment(px, py, x1, y1, x2, y2);
            if (dist <= radius) {
                return true;
            }
        }

        return false;
    }
}
