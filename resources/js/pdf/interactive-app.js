/**
 * Olympia Scholars - Interactive PDF Assignment Master App
 * 
 * Orchestrates PDF viewing, vector drawing engine, eraser, undo/redo,
 * autosave queue, and PDF flattening submission pipeline.
 */

import { PdfViewer } from './pdf-viewer.js';
import { DrawingEngine } from './drawing-engine.js';
import { TextEngine } from './text-engine.js';
import { HistoryManager } from './history-manager.js';
import { StorageManager } from './storage-manager.js';
import { PdfFlattener } from './pdf-flattener.js';

export class InteractivePdfApp {
    constructor(config) {
        this.config = config;
        this.submissionId = config.submissionId;
        this.assignmentId = config.assignmentId;
        this.isLocked = Boolean(config.isLocked);
        this.csrfToken = config.csrfToken;

        // Current tool & style states
        this.currentTool = 'pen';
        this.currentColor = '#111827';
        this.currentWidth = 2.5;
        this.currentFontSize = 17;
        this._isFitMode = false;

        // Mobile pinch-to-zoom and touch gesture states
        this.isPinching = false;
        this._pinchStartDist = 0;
        this._pinchStartZoom = 1.0;
        this._pinchCurrentZoom = 1.0;
        this._pinchStartMid = { x: 0, y: 0 };
        this._pinchFocalRatio = { x: 0.5, y: 0.5 };
        this._pinchPanDelta = { x: 0, y: 0 };
        this._pinchCooldown = 0;
        this.zoomPill = null;
        this._pillTimer = null;

        // Stores strokes per page: Map<number, Array>
        this.strokesMap = new Map();
        this._hydrateInitialStrokes(config.pagesData || {});

        // DOM elements
        this.elements = {
            viewportContainer: document.getElementById('pdf-viewport-container'),
            pageCard: document.getElementById('pdf-page-card'),
            pdfCanvas: document.getElementById('pdf-render-canvas'),
            annotationCanvas: document.getElementById('pdf-annotation-canvas'),
            textLayer: document.getElementById('pdf-text-layer'),
            pageIndicator: document.getElementById('page-indicator'),
            pageInput: document.getElementById('page-jump-input'),
            totalPagesDisplay: document.getElementById('total-pages-display'),
            zoomDisplay: document.getElementById('zoom-level-display'),
            statusBadge: document.getElementById('save-status-badge'),
            loadingOverlay: document.getElementById('loading-overlay'),
            loadingText: document.getElementById('loading-text'),
            submitModal: document.getElementById('submit-confirm-modal'),
            submitModalProgress: document.getElementById('submit-progress-bar'),
        };

        // Sub-modules
        this.history = new HistoryManager(50);
        this.storage = new StorageManager({
            submissionId: this.submissionId,
            savePageUrl: config.savePageUrl,
            csrfToken: this.csrfToken,
            onStatusChange: (status, details) => this._onStorageStatusChange(status, details),
        });

        this.drawingEngine = new DrawingEngine(this.elements.annotationCanvas, {
            penColor: this.currentColor,
            penWidth: this.currentWidth,
            isLocked: this.isLocked,
            fingerDrawEnabled: config.fingerDrawEnabled !== undefined ? config.fingerDrawEnabled : true,
            onStrokeComplete: (stroke) => this._handleStrokeComplete(stroke),
            onStrokesErased: (erased) => this._handleStrokesErased(erased),
        });

        this.textEngine = new TextEngine(this.elements.textLayer, {
            isLocked: this.isLocked,
            defaultColor: this.currentColor,
            defaultFontSize: this.currentFontSize,
            onTextCreated: (stroke) => this._handleTextCreated(stroke),
            onTextChanged: (stroke, old) => this._handleTextChanged(stroke, old),
            onTextMoved: (stroke, old) => this._handleTextMoved(stroke, old),
            onTextDeleted: (stroke, index) => this._handleTextDeleted(stroke, index),
        });

        this.viewer = new PdfViewer({
            container: this.elements.viewportContainer,
            pdfCanvas: this.elements.pdfCanvas,
            annotationCanvas: this.elements.annotationCanvas,
            pdfStreamUrl: config.pdfStreamUrl,
            initialPage: config.initialPage || 1,
            initialZoom: config.initialZoom || 1.0,
            onLoadingChange: (loading, msg) => this._setLoading(loading, msg),
            onPageRendered: (info) => this._handlePageRendered(info),
        });

        this._bindUI();
        this._bindKeyboardShortcuts();
        this._initDocument();
    }

    _hydrateInitialStrokes(pagesData) {
        if (typeof pagesData !== 'object' || pagesData === null) return;
        for (const [pageNum, pageObj] of Object.entries(pagesData)) {
            const num = parseInt(pageNum, 10);
            if (isNaN(num)) continue;

            let strokes = [];
            if (typeof pageObj === 'string') {
                try {
                    const parsed = JSON.parse(pageObj);
                    strokes = parsed.strokes || [];
                } catch (_) {}
            } else if (pageObj && pageObj.annotation_json) {
                try {
                    const parsed = JSON.parse(pageObj.annotation_json);
                    strokes = parsed.strokes || [];
                } catch (_) {}
            } else if (pageObj && Array.isArray(pageObj.strokes)) {
                strokes = pageObj.strokes;
            }
            this.strokesMap.set(num, strokes);
        }
    }

    async _initDocument() {
        try {
            const { totalPages } = await this.viewer.loadDocument();
            if (this.elements.totalPagesDisplay) {
                this.elements.totalPagesDisplay.textContent = totalPages;
            }

            // Check if user has an IndexedDB cached draft for page 1
            const cached = await this.storage.getCachedDraft(this.viewer.currentPage);
            if (cached && Array.isArray(cached.strokes) && cached.strokes.length > 0) {
                const currentServerStrokes = this.strokesMap.get(this.viewer.currentPage) || [];
                if (cached.strokes.length >= currentServerStrokes.length) {
                    this.strokesMap.set(this.viewer.currentPage, cached.strokes);
                }
            }

            // Auto fit-width on mobile / tablet
            const autoZoom = window.innerWidth <= 1024 
                ? this.viewer.computeFitWidthZoom(24) 
                : (this.config.initialZoom || 1.0);

            await this.viewer.renderPage(this.viewer.currentPage, autoZoom);
        } catch (err) {
            console.error('Initialization failed:', err);
            this._showErrorNotification('Could not load assignment PDF document.');
        }
    }

    _handlePageRendered(info) {
        this.drawingEngine.setPageDimensions(info.originalWidth, info.originalHeight, info.zoom);
        this.textEngine.setPageDimensions(info.originalWidth, info.originalHeight, info.zoom);

        // Load strokes for this page
        const strokes = this.strokesMap.get(info.pageNumber) || [];
        this.drawingEngine.setStrokes(strokes);
        this.textEngine.setTextItems(strokes);

        this._updatePageUI();
        this._updateZoomUI();
        this._updateUndoRedoUI();
    }

    _handleStrokeComplete(stroke) {
        const pageNum = this.viewer.currentPage;
        this.history.pushAction(pageNum, { type: 'add_stroke', stroke });
        this.strokesMap.set(pageNum, [...this.drawingEngine.strokes]);

        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, this.drawingEngine.strokes, this.viewer.zoom, this.viewer.totalPages);
    }

    _handleStrokesErased(erasedList) {
        const pageNum = this.viewer.currentPage;
        for (const item of erasedList) {
            this.history.pushAction(pageNum, { 
                type: 'remove_stroke', 
                stroke: item.stroke, 
                index: item.index 
            });
        }
        this.strokesMap.set(pageNum, [...this.drawingEngine.strokes]);

        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, this.drawingEngine.strokes, this.viewer.zoom, this.viewer.totalPages);
    }

    _handleTextCreated(stroke) {
        const pageNum = this.viewer.currentPage;
        this.history.pushAction(pageNum, { type: 'add_stroke', stroke });
        this.strokesMap.set(pageNum, [...this.drawingEngine.strokes]);

        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, this.drawingEngine.strokes, this.viewer.zoom, this.viewer.totalPages);
    }

    _handleTextChanged(stroke, oldStroke) {
        const pageNum = this.viewer.currentPage;
        this.history.pushAction(pageNum, { type: 'modify_stroke', before: oldStroke, after: { ...stroke } });
        this.strokesMap.set(pageNum, [...this.drawingEngine.strokes]);

        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, this.drawingEngine.strokes, this.viewer.zoom, this.viewer.totalPages);
    }

    _handleTextMoved(stroke, oldStroke) {
        const pageNum = this.viewer.currentPage;
        this.history.pushAction(pageNum, { type: 'modify_stroke', before: oldStroke, after: { ...stroke } });
        this.strokesMap.set(pageNum, [...this.drawingEngine.strokes]);

        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, this.drawingEngine.strokes, this.viewer.zoom, this.viewer.totalPages);
    }

    _handleTextDeleted(stroke, index) {
        const pageNum = this.viewer.currentPage;
        this.history.pushAction(pageNum, { type: 'remove_stroke', stroke, index });
        this.strokesMap.set(pageNum, [...this.drawingEngine.strokes]);

        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, this.drawingEngine.strokes, this.viewer.zoom, this.viewer.totalPages);
    }

    // ─────────────────────────────────────────────────────────────
    // TOOLBAR & UI ACTIONS
    // ─────────────────────────────────────────────────────────────

    setTool(tool) {
        this.currentTool = tool;
        this.drawingEngine.setTool(tool);
        this.textEngine.setTool(tool);
        document.querySelectorAll('.tool-btn').forEach(btn => {
            const isTarget = btn.dataset.tool === tool;
            btn.classList.toggle('active-tool', isTarget);
        });
    }

    setPenColor(color) {
        this.currentColor = color;
        this.drawingEngine.setPenColor(color);
        this.textEngine.setColor(color);
        document.querySelectorAll('.color-dot').forEach(dot => {
            const isMatch = dot.dataset.color.toLowerCase() === color.toLowerCase();
            dot.classList.toggle('ring-2', isMatch);
            dot.classList.toggle('ring-offset-2', isMatch);
        });
    }

    setPenWidth(width) {
        this.currentWidth = Number(width);
        this.drawingEngine.setPenWidth(Number(width));
        const fontSizes = { 1.5: 13, 3: 17, 5: 22 };
        const fontSize = fontSizes[Number(width)] || 17;
        this.currentFontSize = fontSize;
        this.textEngine.setFontSize(fontSize);
        document.querySelectorAll('.width-btn').forEach(btn => {
            const isMatch = Number(btn.dataset.width) === Number(width);
            btn.classList.toggle('active-width', isMatch);
        });
    }

    undo() {
        const pageNum = this.viewer.currentPage;
        const strokes = this.drawingEngine.strokes;
        const changed = this.history.undo(pageNum, strokes);
        if (changed) {
            this.strokesMap.set(pageNum, [...strokes]);
            this.drawingEngine.redraw();
            this.textEngine.setTextItems(strokes);
            this._updateUndoRedoUI();
            this.storage.queueSave(pageNum, strokes, this.viewer.zoom, this.viewer.totalPages);
        }
    }

    redo() {
        const pageNum = this.viewer.currentPage;
        const strokes = this.drawingEngine.strokes;
        const changed = this.history.redo(pageNum, strokes);
        if (changed) {
            this.strokesMap.set(pageNum, [...strokes]);
            this.drawingEngine.redraw();
            this.textEngine.setTextItems(strokes);
            this._updateUndoRedoUI();
            this.storage.queueSave(pageNum, strokes, this.viewer.zoom, this.viewer.totalPages);
        }
    }

    clearPage() {
        const pageNum = this.viewer.currentPage;
        const strokes = this.drawingEngine.strokes;
        if (!strokes || strokes.length === 0) return;

        if (!confirm('Clear all handwriting and text on this page?')) {
            return;
        }

        this.textEngine.commitActiveText();
        const clearedCopy = JSON.parse(JSON.stringify(strokes));
        this.history.pushAction(pageNum, { type: 'clear_page', strokes: clearedCopy });
        strokes.length = 0;
        this.strokesMap.set(pageNum, []);
        this.drawingEngine.redraw();
        this.textEngine.clear();
        this._updateUndoRedoUI();
        this.storage.queueSave(pageNum, [], this.viewer.zoom, this.viewer.totalPages);
    }

    async zoomIn() {
        this._isFitMode = false;
        await this.viewer.setZoom(this.viewer.zoom + 0.15);
    }

    async zoomOut() {
        this._isFitMode = false;
        await this.viewer.setZoom(this.viewer.zoom - 0.15);
    }

    async toggleFitWidth() {
        const fitZoom = this.viewer.computeFitWidthZoom(28);
        const currentZoom = this.viewer.zoom;
        const isNearFit = Math.abs(currentZoom - fitZoom) < 0.04;

        if (this._isFitMode || isNearFit) {
            // Already fitted -> toggle back to 100% (1.0)
            this._isFitMode = false;
            await this.viewer.setZoom(1.0);
        } else {
            // Fit to document width
            this._isFitMode = true;
            const targetZoom = Math.abs(fitZoom - 1.0) < 0.04 ? 1.25 : fitZoom;
            await this.viewer.setZoom(targetZoom);
        }
        this._updateZoomUI();
    }

    async fitWidth(forceFit = false) {
        if (!forceFit) {
            return this.toggleFitWidth();
        }
        const fitZoom = this.viewer.computeFitWidthZoom(28);
        this._isFitMode = true;
        await this.viewer.setZoom(fitZoom);
    }

    async goToPage(num, slideDirection = null) {
        const pageNum = Math.max(1, Math.min(this.viewer.totalPages, parseInt(num, 10) || 1));
        if (pageNum !== this.viewer.currentPage) {
            this.textEngine.commitActiveText();
            // Save active page strokes in memory map
            this.strokesMap.set(this.viewer.currentPage, [...this.drawingEngine.strokes]);

            if (slideDirection) {
                this._animatePageSlide(slideDirection);
            }

            await this.viewer.renderPage(pageNum);

            if (slideDirection) {
                this._showMobilePageToast();
            }
        }
    }

    async nextPage(isSlide = false) {
        if (this.viewer.currentPage < this.viewer.totalPages) {
            await this.goToPage(this.viewer.currentPage + 1, isSlide ? 'left' : null);
        }
    }

    async prevPage(isSlide = false) {
        if (this.viewer.currentPage > 1) {
            await this.goToPage(this.viewer.currentPage - 1, isSlide ? 'right' : null);
        }
    }

    _animatePageSlide(direction) {
        const card = this.elements.pageCard;
        if (!card) return;

        const offset = direction === 'left' ? -35 : 35;
        card.style.transition = 'transform 0.15s ease-out, opacity 0.15s ease-out';
        card.style.transform = `translateX(${offset}px)`;
        card.style.opacity = '0.7';

        setTimeout(() => {
            card.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease-out';
            card.style.transform = 'translateX(0)';
            card.style.opacity = '1';
        }, 160);
    }

    _showMobilePageToast() {
        let toast = document.getElementById('mobile-page-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'mobile-page-toast';
            toast.className = 'fixed top-16 left-1/2 -translate-x-1/2 bg-gray-900/85 backdrop-blur-md text-white font-black text-xs px-4 py-1.5 rounded-full shadow-xl pointer-events-none z-50 transition-all duration-300 flex items-center gap-1.5';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span>Page ${this.viewer.currentPage} / ${this.viewer.totalPages}</span>`;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';

        clearTimeout(this._toastTimer);
        this._toastTimer = setTimeout(() => {
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-6px)';
            }
        }, 1200);
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMISSION FLOW
    // ─────────────────────────────────────────────────────────────

    openSubmitModal() {
        if (this.isLocked) return;
        if (this.elements.submitModal) {
            this.elements.submitModal.classList.remove('hidden');
        }
    }

    closeSubmitModal() {
        if (this.elements.submitModal) {
            this.elements.submitModal.classList.add('hidden');
        }
    }

    async confirmSubmit() {
        if (this.isLocked) return;
        this._setLoading(true, 'Flattening and preparing final submission PDF...');

        try {
            // 1. Fetch original PDF binary array
            const pdfResponse = await fetch(this.config.pdfStreamUrl);
            if (!pdfResponse.ok) {
                throw new Error('Could not download original PDF for flattening.');
            }
            const sourcePdfBytes = await pdfResponse.arrayBuffer();

            // 2. Flatten annotations onto original PDF
            const finalPdfBlob = await PdfFlattener.flatten(
                sourcePdfBytes, 
                this.strokesMap, 
                (current, total) => {
                    this._setLoading(true, `Processing page ${current} of ${total}...`);
                }
            );

            this._setLoading(true, 'Uploading final submission to server...');

            // 3. Post multipart payload
            const formData = new FormData();
            formData.append('final_pdf', finalPdfBlob, 'submission_final.pdf');
            formData.append('_token', this.csrfToken);

            const submitResponse = await fetch(this.config.submitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: formData,
            });

            const result = await submitResponse.json();

            if (submitResponse.ok && result.success) {
                alert('Congratulations! Your assignment answers have been successfully submitted.');
                window.location.href = result.redirect_url || '/student/tasks';
            } else {
                throw new Error(result.message || 'Submission failed.');
            }
        } catch (err) {
            console.error('Submission failed:', err);
            alert('Unable to submit assignment. Your local work is preserved. Please try again.');
        } finally {
            this._setLoading(false);
            this.closeSubmitModal();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // INTERNAL UI UPDATES
    // ─────────────────────────────────────────────────────────────

    _setLoading(loading, message = 'Loading...') {
        if (!this.elements.loadingOverlay) return;
        this.elements.loadingOverlay.classList.toggle('hidden', !loading);
        if (this.elements.loadingText) {
            this.elements.loadingText.textContent = message;
        }
    }

    _updatePageUI() {
        if (this.elements.pageIndicator) {
            this.elements.pageIndicator.textContent = `Page ${this.viewer.currentPage} / ${this.viewer.totalPages}`;
        }
        if (this.elements.pageInput) {
            this.elements.pageInput.value = this.viewer.currentPage;
        }
    }

    _updateZoomUI() {
        if (this.elements.zoomDisplay) {
            this.elements.zoomDisplay.textContent = `${Math.round(this.viewer.zoom * 100)}%`;
        }

        const fitBtn = document.getElementById('btn-fit-width');
        if (fitBtn) {
            const fitZoom = this.viewer.computeFitWidthZoom(28);
            const isFitted = this._isFitMode || (Math.abs(this.viewer.zoom - fitZoom) < 0.04 && Math.abs(this.viewer.zoom - 1.0) >= 0.05);

            if (isFitted) {
                fitBtn.textContent = '100%';
                fitBtn.title = 'Click to reset to 100% zoom';
                fitBtn.classList.add('bg-blue-50', 'text-blue-700', 'ring-1', 'ring-blue-300');
                fitBtn.classList.remove('bg-gray-100', 'text-gray-700');
            } else {
                fitBtn.textContent = 'Fit';
                fitBtn.title = 'Click to fit page to width';
                fitBtn.classList.remove('bg-blue-50', 'text-blue-700', 'ring-1', 'ring-blue-300');
                fitBtn.classList.add('bg-gray-100', 'text-gray-700');
            }
        }
    }

    _updateUndoRedoUI() {
        const pageNum = this.viewer.currentPage;
        const undoBtn = document.getElementById('btn-undo');
        const redoBtn = document.getElementById('btn-redo');

        if (undoBtn) undoBtn.disabled = !this.history.canUndo(pageNum) || this.isLocked;
        if (redoBtn) redoBtn.disabled = !this.history.canRedo(pageNum) || this.isLocked;
    }

    _onStorageStatusChange(status, details) {
        if (!this.elements.statusBadge) return;

        const badge = this.elements.statusBadge;
        // Clean, minimal text indicator without any pill/bubble background or border
        badge.className = 'text-xs font-semibold text-gray-500 flex items-center gap-1.5 transition-colors select-none';

        if (status === 'saving') {
            badge.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> <span>Saving...</span>`;
        } else if (status === 'saved') {
            const timeStr = details?.lastSavedAt ? ` (${details.lastSavedAt})` : '';
            badge.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span> <span>Saved${timeStr}</span>`;
        } else if (status === 'offline') {
            badge.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400"></span> <span>Saved locally</span>`;
        } else if (status === 'error') {
            badge.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500"></span> <span class="text-rose-600">Preserved locally</span>`;
        } else if (status === 'locked') {
            badge.innerHTML = `<span class="text-gray-400 text-[11px]">🔒</span> <span>Locked</span>`;
        }
    }

    _showErrorNotification(msg) {
        alert(msg);
    }

    _bindUI() {
        // Tool Selection
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.addEventListener('click', () => this.setTool(btn.dataset.tool));
        });

        // Color Dots
        document.querySelectorAll('.color-dot').forEach(dot => {
            dot.addEventListener('click', () => this.setPenColor(dot.dataset.color));
        });

        // Width Buttons
        document.querySelectorAll('.width-btn').forEach(btn => {
            btn.addEventListener('click', () => this.setPenWidth(btn.dataset.width));
        });

        // Action Buttons
        document.getElementById('btn-undo')?.addEventListener('click', () => this.undo());
        document.getElementById('btn-redo')?.addEventListener('click', () => this.redo());
        document.getElementById('btn-clear')?.addEventListener('click', () => this.clearPage());
        document.getElementById('btn-prev-page')?.addEventListener('click', () => this.prevPage());
        document.getElementById('btn-next-page')?.addEventListener('click', () => this.nextPage());
        document.getElementById('btn-zoom-in')?.addEventListener('click', () => this.zoomIn());
        document.getElementById('btn-zoom-out')?.addEventListener('click', () => this.zoomOut());
        document.getElementById('btn-fit-width')?.addEventListener('click', () => this.toggleFitWidth());
        document.getElementById('zoom-level-display')?.addEventListener('click', async () => {
            this._isFitMode = false;
            await this.viewer.setZoom(1.0);
        });
        document.getElementById('btn-submit-open')?.addEventListener('click', () => this.openSubmitModal());
        document.getElementById('btn-submit-cancel')?.addEventListener('click', () => this.closeSubmitModal());
        document.getElementById('btn-submit-confirm')?.addEventListener('click', () => this.confirmSubmit());

        // Page Jump Input
        if (this.elements.pageInput) {
            this.elements.pageInput.addEventListener('change', (e) => this.goToPage(e.target.value));
        }

        // Window resize debounce
        let resizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth <= 1024) {
                    this.fitWidth(true);
                } else {
                    this.viewer.renderPage(this.viewer.currentPage, this.viewer.zoom);
                }
            }, 250);
        });

        // Mobile touch swipe gesture
        this._bindTouchPageSwipe();

        // Mobile touch pinch-to-zoom gesture
        this._bindTouchPinchZoom();

        // Double tap zoom
        this._bindDoubleTapZoom();

        // Desktop trackpad / Ctrl+wheel zoom
        this._bindWheelZoom();
    }

    _ensureZoomPill() {
        if (this.zoomPill) return this.zoomPill;
        let pill = document.getElementById('pinch-zoom-pill');
        if (!pill) {
            pill = document.createElement('div');
            pill.id = 'pinch-zoom-pill';
            pill.className = 'fixed top-16 left-1/2 -translate-x-1/2 z-50 bg-gray-900/90 text-white backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-black shadow-2xl tracking-wider pointer-events-none transition-all duration-150 opacity-0 scale-90 flex items-center gap-2 border border-white/10 select-none';
            pill.innerHTML = `
                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span id="pinch-zoom-text">100%</span>
            `;
            document.body.appendChild(pill);
        }
        this.zoomPill = pill;
        return pill;
    }

    _showZoomPill(percentage) {
        const pill = this._ensureZoomPill();
        const text = pill.querySelector('#pinch-zoom-text');
        if (text) text.textContent = `${percentage}%`;
        pill.classList.remove('opacity-0', 'scale-90');
        pill.classList.add('opacity-100', 'scale-100');
        clearTimeout(this._pillTimer);
    }

    _hideZoomPill() {
        clearTimeout(this._pillTimer);
        this._pillTimer = setTimeout(() => {
            if (this.zoomPill) {
                this.zoomPill.classList.remove('opacity-100', 'scale-100');
                this.zoomPill.classList.add('opacity-0', 'scale-90');
            }
        }, 500);
    }

    _bindTouchPinchZoom() {
        const container = this.elements.viewportContainer;
        if (!container) return;

        const onTouchStart = (e) => {
            if (e.touches.length === 2) {
                const t1 = e.touches[0];
                const t2 = e.touches[1];
                const dist = Math.hypot(t2.clientX - t1.clientX, t2.clientY - t1.clientY);
                if (dist < 10) return;

                this.isPinching = true;
                this._pinchStartDist = dist;
                this._pinchStartZoom = this.viewer.zoom;
                this._pinchCurrentZoom = this.viewer.zoom;
                this._pinchStartMid = {
                    x: (t1.clientX + t2.clientX) / 2,
                    y: (t1.clientY + t2.clientY) / 2,
                };
                this._pinchPanDelta = { x: 0, y: 0 };

                // Cancel any drawing stroke or active text editing
                if (this.drawingEngine) {
                    this.drawingEngine.setIsPinching(true);
                }
                if (this.textEngine) {
                    this.textEngine.commitActiveText();
                }
                if (document.activeElement && ['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    document.activeElement.blur();
                }

                // Compute focal point relative to pageCard
                const card = this.elements.pageCard || document.getElementById('pdf-page-card');
                if (card) {
                    const rect = card.getBoundingClientRect();
                    this._pinchFocalRatio = {
                        x: rect.width > 0 ? Math.max(0, Math.min(1, (this._pinchStartMid.x - rect.left) / rect.width)) : 0.5,
                        y: rect.height > 0 ? Math.max(0, Math.min(1, (this._pinchStartMid.y - rect.top) / rect.height)) : 0.5,
                    };
                }

                this._showZoomPill(Math.round(this._pinchStartZoom * 100));
            }
        };

        const onTouchMove = (e) => {
            if (!this.isPinching || e.touches.length !== 2) return;

            // Stop browser default rubber-banding / page pinch
            e.preventDefault();

            const t1 = e.touches[0];
            const t2 = e.touches[1];
            const dist = Math.hypot(t2.clientX - t1.clientX, t2.clientY - t1.clientY);
            if (dist < 10) return;

            const scaleRatio = dist / this._pinchStartDist;
            const minZoom = 0.5;
            const maxZoom = 3.0;
            const targetZoom = Math.max(minZoom, Math.min(maxZoom, this._pinchStartZoom * scaleRatio));
            this._pinchCurrentZoom = targetZoom;

            const visualScale = targetZoom / this._pinchStartZoom;
            const currentMidX = (t1.clientX + t2.clientX) / 2;
            const currentMidY = (t1.clientY + t2.clientY) / 2;
            const panX = currentMidX - this._pinchStartMid.x;
            const panY = currentMidY - this._pinchStartMid.y;
            this._pinchPanDelta = { x: panX, y: panY };

            const card = this.elements.pageCard || document.getElementById('pdf-page-card');
            if (card) {
                const originX = (this._pinchFocalRatio.x * 100).toFixed(2);
                const originY = (this._pinchFocalRatio.y * 100).toFixed(2);
                card.style.transformOrigin = `${originX}% ${originY}%`;
                card.style.transform = `scale(${visualScale}) translate3d(${panX / visualScale}px, ${panY / visualScale}px, 0)`;
                card.style.transition = 'none';
            }

            this._showZoomPill(Math.round(targetZoom * 100));
        };

        const onTouchEnd = async (e) => {
            if (!this.isPinching) return;

            if (e.touches.length < 2) {
                this.isPinching = false;
                this._pinchCooldown = Date.now() + 350;

                const targetZoom = Number(this._pinchCurrentZoom.toFixed(2));
                const card = this.elements.pageCard || document.getElementById('pdf-page-card');

                if (card) {
                    card.style.transform = '';
                    card.style.transformOrigin = '';
                    card.style.transition = '';
                }

                this._hideZoomPill();

                setTimeout(() => {
                    if (this.drawingEngine) {
                        this.drawingEngine.setIsPinching(false);
                    }
                }, 200);

                const zoomDiff = Math.abs(targetZoom - this.viewer.zoom);
                const hasPan = Math.abs(this._pinchPanDelta.x) > 5 || Math.abs(this._pinchPanDelta.y) > 5;

                if (zoomDiff >= 0.04) {
                    this._isFitMode = false;

                    const containerRect = container.getBoundingClientRect();
                    const newCardWidth = this.viewer.originalPageDimensions.width * targetZoom;
                    const newCardHeight = this.viewer.originalPageDimensions.height * targetZoom;

                    const targetX = this._pinchFocalRatio.x * newCardWidth;
                    const targetY = this._pinchFocalRatio.y * newCardHeight;

                    await this.viewer.setZoom(targetZoom);

                    // Re-position scroll so focal point is preserved
                    container.scrollLeft = Math.max(0, targetX - (this._pinchStartMid.x - containerRect.left) - this._pinchPanDelta.x);
                    container.scrollTop = Math.max(0, targetY - (this._pinchStartMid.y - containerRect.top) - this._pinchPanDelta.y);

                    this.storage.queueSave(
                        this.viewer.currentPage,
                        this.drawingEngine.strokes,
                        this.viewer.zoom,
                        this.viewer.totalPages
                    );
                } else if (hasPan) {
                    // Panned with two fingers without significant zoom change
                    container.scrollLeft = Math.max(0, container.scrollLeft - this._pinchPanDelta.x);
                    container.scrollTop = Math.max(0, container.scrollTop - this._pinchPanDelta.y);
                    this._updateZoomUI();
                } else {
                    this._updateZoomUI();
                }
            }
        };

        container.addEventListener('touchstart', onTouchStart, { passive: false });
        container.addEventListener('touchmove', onTouchMove, { passive: false });
        container.addEventListener('touchend', onTouchEnd, { passive: false });
        container.addEventListener('touchcancel', onTouchEnd, { passive: false });
    }

    _bindDoubleTapZoom() {
        const container = this.elements.viewportContainer;
        if (!container) return;

        let lastTapTime = 0;
        let lastTapX = 0;
        let lastTapY = 0;

        container.addEventListener('touchend', async (e) => {
            if (this.isPinching || Date.now() < this._pinchCooldown) return;
            if (e.changedTouches.length !== 1) return;

            const target = e.target;
            if (['BUTTON', 'INPUT', 'TEXTAREA', 'A'].includes(target.tagName) ||
                target.closest('.tool-btn') || target.closest('.color-dot') || target.closest('.pdf-text-box')) {
                return;
            }

            // Only double tap if not in active drawing mode (e.g. locked mode, or tool is eraser/text, or tap outside canvas)
            if (!this.isLocked && this.currentTool === 'pen') {
                return;
            }

            const touch = e.changedTouches[0];
            const now = Date.now();
            const timeDiff = now - lastTapTime;
            const dist = Math.hypot(touch.clientX - lastTapX, touch.clientY - lastTapY);

            if (timeDiff < 320 && dist < 30) {
                lastTapTime = 0;
                if (this.viewer.zoom > 1.25) {
                    await this.fitWidth();
                } else {
                    const newZoom = 1.75;
                    this._isFitMode = false;
                    await this.viewer.setZoom(newZoom);
                    this._showZoomPill(175);
                    this._hideZoomPill();
                }
            } else {
                lastTapTime = now;
                lastTapX = touch.clientX;
                lastTapY = touch.clientY;
            }
        }, { passive: true });
    }

    _bindWheelZoom() {
        const container = this.elements.viewportContainer;
        if (!container) return;

        container.addEventListener('wheel', async (e) => {
            if (e.ctrlKey) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.15 : -0.15;
                const newZoom = Math.max(0.5, Math.min(3.0, Number((this.viewer.zoom + delta).toFixed(2))));
                if (newZoom !== this.viewer.zoom) {
                    this._isFitMode = false;
                    await this.viewer.setZoom(newZoom);
                    this._showZoomPill(Math.round(newZoom * 100));
                    this._hideZoomPill();
                }
            }
        }, { passive: false });
    }

    _bindTouchPageSwipe() {
        const container = this.elements.viewportContainer;
        if (!container) return;

        let touchStartX = 0;
        let touchStartY = 0;
        let touchStartTime = 0;
        let isSwipeCandidate = false;

        container.addEventListener('touchstart', (e) => {
            // Only single finger touch and not pinching or in pinch cooldown
            if (e.touches.length !== 1 || this.isPinching || Date.now() < this._pinchCooldown) {
                isSwipeCandidate = false;
                return;
            }

            // Do not swipe if touch starts on form controls, text boxes, or toolbar
            if (['INPUT', 'TEXTAREA', 'BUTTON', 'A', 'SELECT'].includes(e.target.tagName)) {
                isSwipeCandidate = false;
                return;
            }
            if (e.target.closest('.pdf-text-box') || e.target.closest('.tool-btn') || e.target.closest('.color-dot')) {
                isSwipeCandidate = false;
                return;
            }

            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            touchStartTime = Date.now();
            isSwipeCandidate = true;
        }, { passive: true });

        container.addEventListener('touchmove', (e) => {
            if (!isSwipeCandidate || e.touches.length !== 1 || this.isPinching || Date.now() < this._pinchCooldown) return;

            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const dx = currentX - touchStartX;
            const dy = currentY - touchStartY;

            // If user is predominantly scrolling vertically, cancel horizontal swipe candidate
            if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 20) {
                isSwipeCandidate = false;
            }
        }, { passive: true });

        container.addEventListener('touchend', (e) => {
            if (!isSwipeCandidate || e.changedTouches.length !== 1 || this.isPinching || Date.now() < this._pinchCooldown) return;
            isSwipeCandidate = false;

            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            const dx = endX - touchStartX;
            const dy = endY - touchStartY;
            const duration = Date.now() - touchStartTime;

            // Swipe conditions:
            // 1. Min 45px horizontal movement
            // 2. Clear horizontal orientation (horizontal > vertical * 1.25)
            // 3. Fast enough gesture (within 650ms)
            if (Math.abs(dx) >= 45 && Math.abs(dx) > Math.abs(dy) * 1.25 && duration < 650) {
                // If container is horizontally scrolled, check bounds
                const isAtRightEdge = container.scrollLeft + container.clientWidth >= container.scrollWidth - 35;
                const isAtLeftEdge = container.scrollLeft <= 35;
                const hasNoHorizontalOverflow = container.scrollWidth <= container.clientWidth + 10;

                if (dx < 0) {
                    // Swiped Left -> Slide to Next Page
                    if (hasNoHorizontalOverflow || isAtRightEdge) {
                        this.nextPage(true);
                    }
                } else {
                    // Swiped Right -> Slide to Previous Page
                    if (hasNoHorizontalOverflow || isAtLeftEdge) {
                        this.prevPage(true);
                    }
                }
            }
        }, { passive: true });
    }

    _bindKeyboardShortcuts() {
        window.addEventListener('keydown', (e) => {
            // Ignore when typing in text inputs
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

            if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.nextPage();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.prevPage();
            } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
                e.preventDefault();
                if (e.shiftKey) {
                    this.redo();
                } else {
                    this.undo();
                }
            } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
                e.preventDefault();
                this.redo();
            } else if (e.key.toLowerCase() === 'p') {
                this.setTool('pen');
            } else if (e.key.toLowerCase() === 'e') {
                this.setTool('eraser');
            } else if (e.key.toLowerCase() === 't') {
                this.setTool('text');
            }
        });
    }
}

// Global factory helper
window.initInteractivePdfAssignment = function(config) {
    return new InteractivePdfApp(config);
};
