/**
 * Olympia Scholars - PDF Flattener Pipeline
 * 
 * Uses pdf-lib in browser to merge transparent high-resolution canvas annotations
 * directly into the original PDF pages, producing a single flattened submission document.
 */

import { PDFDocument } from 'pdf-lib';

export class PdfFlattener {
    /**
     * Flatten student handwriting annotations onto the original PDF.
     * 
     * @param {ArrayBuffer} sourcePdfBytes Binary buffer of original assignment PDF
     * @param {Map<number, Array>} pageStrokesMap Map of pageNumber -> strokes array
     * @param {Function} onProgress Optional progress callback (current, total)
     * @returns {Promise<Blob>} The merged PDF as a Blob
     */
    static async flatten(sourcePdfBytes, pageStrokesMap, onProgress = () => {}) {
        const pdfDoc = await PDFDocument.load(sourcePdfBytes);
        const pages = pdfDoc.getPages();
        const total = pages.length;

        for (let i = 0; i < total; i++) {
            const pageNum = i + 1;
            onProgress(pageNum, total);

            const strokes = pageStrokesMap.get(pageNum);
            if (!strokes || strokes.length === 0) {
                continue;
            }

            const page = pages[i];
            const pageWidth = page.getWidth();
            const pageHeight = page.getHeight();

            // Render annotations to an offscreen high-DPI canvas (2x for crisp 144 DPI export)
            const exportScale = 2.0;
            const offscreen = document.createElement('canvas');
            offscreen.width = Math.round(pageWidth * exportScale);
            offscreen.height = Math.round(pageHeight * exportScale);

            const ctx = offscreen.getContext('2d');
            ctx.scale(exportScale, exportScale);

            // Draw all strokes & text annotations for this page
            for (const stroke of strokes) {
                if (stroke.tool === 'text') {
                    this._drawText(ctx, stroke, pageWidth, pageHeight);
                } else {
                    this._drawStroke(ctx, stroke, pageWidth, pageHeight);
                }
            }

            // Convert canvas to PNG blob bytes
            const dataUrl = offscreen.toDataURL('image/png');
            const pngBytes = await fetch(dataUrl).then(res => res.arrayBuffer());

            // Embed PNG onto PDF page
            const embeddedPng = await pdfDoc.embedPng(pngBytes);
            page.drawImage(embeddedPng, {
                x: 0,
                y: 0,
                width: pageWidth,
                height: pageHeight,
            });
        }

        const flattenedPdfBytes = await pdfDoc.save();
        return new Blob([flattenedPdfBytes], { type: 'application/pdf' });
    }

    static _drawStroke(ctx, stroke, pageWidth, pageHeight) {
        const pts = stroke.points;
        if (!pts || pts.length === 0) return;

        ctx.save();
        ctx.strokeStyle = stroke.color || '#111827';
        ctx.fillStyle = stroke.color || '#111827';
        ctx.lineWidth = stroke.width || 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        if (pts.length === 1) {
            const x = pts[0][0] * pageWidth;
            const y = pts[0][1] * pageHeight;
            ctx.beginPath();
            ctx.arc(x, y, ctx.lineWidth / 2, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
            return;
        }

        ctx.beginPath();
        const startX = pts[0][0] * pageWidth;
        const startY = pts[0][1] * pageHeight;
        ctx.moveTo(startX, startY);

        for (let i = 1; i < pts.length - 1; i++) {
            const xc = ((pts[i][0] + pts[i + 1][0]) / 2) * pageWidth;
            const yc = ((pts[i][1] + pts[i + 1][1]) / 2) * pageHeight;
            const x = pts[i][0] * pageWidth;
            const y = pts[i][1] * pageHeight;
            ctx.quadraticCurveTo(x, y, xc, yc);
        }

        const lastX = pts[pts.length - 1][0] * pageWidth;
        const lastY = pts[pts.length - 1][1] * pageHeight;
        ctx.lineTo(lastX, lastY);
        ctx.stroke();

        ctx.restore();
    }

    static _drawText(ctx, stroke, pageWidth, pageHeight) {
        if (!stroke.text || stroke.text.trim() === '') return;

        ctx.save();
        const fontSize = stroke.fontSize || 16;
        ctx.font = `${fontSize}px "Plus Jakarta Sans", system-ui, -apple-system, sans-serif`;
        ctx.fillStyle = stroke.color || '#111827';
        ctx.textBaseline = 'top';

        const startX = stroke.x * pageWidth;
        const startY = stroke.y * pageHeight;
        const lineHeight = fontSize * 1.35;
        const maxBoxWidth = Math.max(100, pageWidth - startX - 16);

        const rawLines = stroke.text.split('\n');
        let currentY = startY;

        for (const rawLine of rawLines) {
            const words = rawLine.split(' ');
            let currentLine = '';

            for (let n = 0; n < words.length; n++) {
                const testLine = currentLine + (currentLine ? ' ' : '') + words[n];
                const metrics = ctx.measureText(testLine);
                if (metrics.width > maxBoxWidth && currentLine !== '') {
                    ctx.fillText(currentLine, startX, currentY);
                    currentLine = words[n];
                    currentY += lineHeight;
                } else {
                    currentLine = testLine;
                }
            }
            if (currentLine) {
                ctx.fillText(currentLine, startX, currentY);
                currentY += lineHeight;
            }
        }

        ctx.restore();
    }
}
