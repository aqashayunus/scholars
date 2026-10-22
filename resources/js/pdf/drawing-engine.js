/**
 * Olympia Scholars - Drawing Engine
 * 
 * Handles W3C Pointer Events with Apple Pencil / stylus distinction, pressure sensitivity,
 * smooth quadratic curve interpolation, and high-DPI canvas synchronization.
 */

import { CoordinateNormalizer } from './coordinate-normalizer.js';
import { EraserEngine } from './eraser-engine.js';

export class DrawingEngine {
    constructor(canvas, config = {}) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.eraser = new EraserEngine(config.eraserRadius || 14);

        // State
        this.tool = 'pen'; // 'pen' | 'eraser'
        this.penColor = config.penColor || '#111827';
        this.penWidth = config.penWidth || 2.5;
        this.fingerDrawEnabled = config.fingerDrawEnabled !== undefined ? config.fingerDrawEnabled : true;
        this.isLocked = config.isLocked || false;
        this.isPinching = false;

        // Active document dimensions
        this.pdfOriginalWidth = 595;
        this.pdfOriginalHeight = 842;
        this.zoom = 1.0;
        this.dpr = window.devicePixelRatio || 1;

        // Current page strokes and active drawing state
        this.strokes = [];
        this.activeStroke = null;
        this.isDrawing = false;
        this.activePointerId = null;

        // Callbacks
        this.onStrokeComplete = config.onStrokeComplete || (() => {});
        this.onStrokesErased = config.onStrokesErased || (() => {});
        this.onTextCreate = config.onTextCreate || (() => {});

        this._bindPointerEvents();
    }

    setPageDimensions(origWidth, origHeight, zoom) {
        this.pdfOriginalWidth = origWidth;
        this.pdfOriginalHeight = origHeight;
        this.zoom = zoom;
        this.dpr = window.devicePixelRatio || 1;
    }

    setStrokes(strokes) {
        this.strokes = strokes || [];
        this.redraw();
    }

    setTool(tool) {
        this.tool = tool;
        if (this.canvas) {
            this.canvas.style.cursor = tool === 'text' ? 'text' : (tool === 'eraser' ? 'crosshair' : 'crosshair');
        }
    }

    setPenColor(color) {
        this.penColor = color;
    }

    setPenWidth(width) {
        this.penWidth = width;
    }

    setFingerDrawEnabled(enabled) {
        this.fingerDrawEnabled = enabled;
    }

    setLocked(locked) {
        this.isLocked = locked;
    }

    setIsPinching(pinching) {
        this.isPinching = Boolean(pinching);
        if (this.isPinching) {
            this.cancelActiveStroke();
        }
    }

    cancelActiveStroke() {
        if (this.isDrawing || this.activeStroke) {
            this.isDrawing = false;
            this.activeStroke = null;
            if (this.activePointerId !== null) {
                try {
                    this.canvas.releasePointerCapture(this.activePointerId);
                } catch (_) {}
                this.activePointerId = null;
            }
            this.redraw();
        }
    }

    _bindPointerEvents() {
        this.canvas.addEventListener('pointerdown', (e) => this._onPointerDown(e));
        this.canvas.addEventListener('pointermove', (e) => this._onPointerMove(e));
        this.canvas.addEventListener('pointerup', (e) => this._onPointerUp(e));
        this.canvas.addEventListener('pointercancel', (e) => this._onPointerUp(e));
    }

    _onPointerDown(e) {
        if (this.isLocked || this.isPinching) return;

        // Check if drawing is allowed for this pointer type
        const isPen = e.pointerType === 'pen';
        const isMouse = e.pointerType === 'mouse' && e.button === 0;
        const isTouch = e.pointerType === 'touch' && this.fingerDrawEnabled;

        if (!isPen && !isMouse && !isTouch) {
            // Let single touch naturally scroll / pan the viewport
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        const pt = CoordinateNormalizer.screenToPdf(
            e.clientX,
            e.clientY,
            this.canvas,
            this.pdfOriginalWidth,
            this.pdfOriginalHeight
        );

        if (this.tool === 'text') {
            return;
        }

        this.isDrawing = true;
        this.activePointerId = e.pointerId;
        try {
            this.canvas.setPointerCapture(e.pointerId);
        } catch (_) {}

        const pressure = (e.pressure && e.pressure > 0) ? e.pressure : 0.5;

        if (this.tool === 'pen') {
            this.activeStroke = {
                id: 'st_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                tool: 'pen',
                color: this.penColor,
                width: this.penWidth,
                points: [[pt.normX, pt.normY, pressure]],
            };

            // Draw initial point
            const currentWidth = this.pdfOriginalWidth * this.zoom;
            const currentHeight = this.pdfOriginalHeight * this.zoom;
            const rendered = CoordinateNormalizer.normToRendered(pt.normX, pt.normY, currentWidth, currentHeight);

            this.ctx.save();
            this.ctx.fillStyle = this.penColor;
            this.ctx.beginPath();
            this.ctx.arc(rendered.x, rendered.y, (this.penWidth * (0.8 + pressure * 0.4)) / 2, 0, Math.PI * 2);
            this.ctx.fill();
            this.ctx.restore();
        } else if (this.tool === 'eraser') {
            const currentWidth = this.pdfOriginalWidth * this.zoom;
            const currentHeight = this.pdfOriginalHeight * this.zoom;
            const erased = this.eraser.eraseAt(pt.normX, pt.normY, currentWidth, currentHeight, this.strokes);
            if (erased.length > 0) {
                this.redraw();
                this.onStrokesErased(erased);
            }
        }
    }

    _onPointerMove(e) {
        if (!this.isDrawing || e.pointerId !== this.activePointerId || this.isLocked || this.isPinching) return;

        e.preventDefault();
        e.stopPropagation();

        const pt = CoordinateNormalizer.screenToPdf(
            e.clientX,
            e.clientY,
            this.canvas,
            this.pdfOriginalWidth,
            this.pdfOriginalHeight
        );

        const pressure = (e.pressure && e.pressure > 0) ? e.pressure : 0.5;
        const currentWidth = this.pdfOriginalWidth * this.zoom;
        const currentHeight = this.pdfOriginalHeight * this.zoom;

        if (this.tool === 'pen' && this.activeStroke) {
            const pts = this.activeStroke.points;
            pts.push([pt.normX, pt.normY, pressure]);

            // Draw smoothed segment incrementally
            if (pts.length >= 3) {
                const prev2 = CoordinateNormalizer.normToRendered(pts[pts.length - 3][0], pts[pts.length - 3][1], currentWidth, currentHeight);
                const prev1 = CoordinateNormalizer.normToRendered(pts[pts.length - 2][0], pts[pts.length - 2][1], currentWidth, currentHeight);
                const curr = CoordinateNormalizer.normToRendered(pts[pts.length - 1][0], pts[pts.length - 1][1], currentWidth, currentHeight);

                const mid1 = { x: (prev2.x + prev1.x) / 2, y: (prev2.y + prev1.y) / 2 };
                const mid2 = { x: (prev1.x + curr.x) / 2, y: (prev1.y + curr.y) / 2 };

                this.ctx.save();
                this.ctx.strokeStyle = this.penColor;
                this.ctx.lineWidth = this.penWidth * (0.8 + pressure * 0.4);
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';

                this.ctx.beginPath();
                this.ctx.moveTo(mid1.x, mid1.y);
                this.ctx.quadraticCurveTo(prev1.x, prev1.y, mid2.x, mid2.y);
                this.ctx.stroke();
                this.ctx.restore();
            }
        } else if (this.tool === 'eraser') {
            const erased = this.eraser.eraseAt(pt.normX, pt.normY, currentWidth, currentHeight, this.strokes);
            if (erased.length > 0) {
                this.redraw();
                this.onStrokesErased(erased);
            }
        }
    }

    _onPointerUp(e) {
        if (this.isPinching) {
            this.cancelActiveStroke();
            return;
        }
        if (!this.isDrawing || (this.activePointerId !== null && e.pointerId !== this.activePointerId)) return;

        this.isDrawing = false;
        try {
            if (this.activePointerId !== null) {
                this.canvas.releasePointerCapture(this.activePointerId);
            }
        } catch (_) {}
        this.activePointerId = null;

        if (this.tool === 'pen' && this.activeStroke) {
            this.strokes.push(this.activeStroke);
            const completed = this.activeStroke;
            this.activeStroke = null;
            this.onStrokeComplete(completed);
        }
    }

    /**
     * Clear and redraw all strokes on the canvas at the current resolution.
     */
    redraw() {
        const width = this.canvas.width / this.dpr;
        const height = this.canvas.height / this.dpr;

        this.ctx.clearRect(0, 0, width, height);

        const currentWidth = this.pdfOriginalWidth * this.zoom;
        const currentHeight = this.pdfOriginalHeight * this.zoom;

        for (const stroke of this.strokes) {
            this._renderSingleStroke(stroke, currentWidth, currentHeight);
        }
    }

    _renderSingleStroke(stroke, currentWidth, currentHeight) {
        if (!stroke || stroke.tool === 'text') return;
        const pts = stroke.points;
        if (!pts || pts.length === 0) return;

        this.ctx.save();
        this.ctx.strokeStyle = stroke.color || '#111827';
        this.ctx.fillStyle = stroke.color || '#111827';
        this.ctx.lineWidth = stroke.width || 2.5;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';

        if (pts.length === 1) {
            const p = CoordinateNormalizer.normToRendered(pts[0][0], pts[0][1], currentWidth, currentHeight);
            this.ctx.beginPath();
            this.ctx.arc(p.x, p.y, this.ctx.lineWidth / 2, 0, Math.PI * 2);
            this.ctx.fill();
            this.ctx.restore();
            return;
        }

        this.ctx.beginPath();
        const start = CoordinateNormalizer.normToRendered(pts[0][0], pts[0][1], currentWidth, currentHeight);
        this.ctx.moveTo(start.x, start.y);

        for (let i = 1; i < pts.length - 1; i++) {
            const xc = ((pts[i][0] + pts[i + 1][0]) / 2) * currentWidth;
            const yc = ((pts[i][1] + pts[i + 1][1]) / 2) * currentHeight;
            const x = pts[i][0] * currentWidth;
            const y = pts[i][1] * currentHeight;
            this.ctx.quadraticCurveTo(x, y, xc, yc);
        }

        const last = CoordinateNormalizer.normToRendered(pts[pts.length - 1][0], pts[pts.length - 1][1], currentWidth, currentHeight);
        this.ctx.lineTo(last.x, last.y);
        this.ctx.stroke();

        this.ctx.restore();
    }
}
