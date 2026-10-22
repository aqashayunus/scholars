/**
 * Olympia Scholars - PDF.js Multi-Page Viewer
 * 
 * Manages Mozilla PDF.js document lifecycle, page navigation, aspect-ratio preservation,
 * zoom calculation, and aligned rendering between the PDF canvas and annotation canvas.
 */

export class PdfViewer {
    constructor(config) {
        this.container = config.container;
        this.pdfCanvas = config.pdfCanvas;
        this.annotationCanvas = config.annotationCanvas;
        this.pdfStreamUrl = config.pdfStreamUrl;
        this.onPageRendered = config.onPageRendered || (() => {});
        this.onLoadingChange = config.onLoadingChange || (() => {});

        this.pdfDoc = null;
        this.currentPage = config.initialPage || 1;
        this.totalPages = 1;
        this.zoom = config.initialZoom || 1.0;
        this.renderTask = null;
        this.isRendering = false;
        this.originalPageDimensions = { width: 595, height: 842 };

        this._setupPdfJsWorker();
    }

    _setupPdfJsWorker() {
        if (!window.pdfjsLib) return;
        if (!window.pdfjsLib.GlobalWorkerOptions.workerSrc) {
            // Use local worker asset with CDN fallback
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = '/vendor/pdfjs/pdf.worker.min.js';
        }
    }

    async loadDocument() {
        if (!window.pdfjsLib) {
            throw new Error('PDF.js library is not loaded.');
        }

        this.onLoadingChange(true, 'Loading assignment document...');

        try {
            const loadingTask = window.pdfjsLib.getDocument({
                url: this.pdfStreamUrl,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true,
                withCredentials: true,
            });

            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;

            // Load first page to discover native PDF dimensions
            const firstPage = await this.pdfDoc.getPage(1);
            const unscaledViewport = firstPage.getViewport({ scale: 1.0 });
            this.originalPageDimensions = {
                width: unscaledViewport.width,
                height: unscaledViewport.height,
            };

            return {
                totalPages: this.totalPages,
                dimensions: this.originalPageDimensions,
            };
        } catch (err) {
            console.error('Failed to load PDF document:', err);
            throw err;
        } finally {
            this.onLoadingChange(false);
        }
    }

    /**
     * Compute zoom factor to fit document width into current container.
     */
    computeFitWidthZoom(padding = 32) {
        if (!this.container || !this.originalPageDimensions.width) return 1.0;
        const availableWidth = this.container.clientWidth - padding;
        const factor = availableWidth / this.originalPageDimensions.width;
        // Clamp between 0.5 and 2.5
        return Math.max(0.5, Math.min(2.5, Number(factor.toFixed(2))));
    }

    async renderPage(pageNumber, zoom = this.zoom) {
        if (!this.pdfDoc) return;
        if (pageNumber < 1 || pageNumber > this.totalPages) return;

        // Cancel in-flight render
        if (this.renderTask) {
            try {
                this.renderTask.cancel();
            } catch (_) {}
            this.renderTask = null;
        }

        this.currentPage = pageNumber;
        this.zoom = zoom;
        this.isRendering = true;
        this.onLoadingChange(true, `Rendering page ${pageNumber}...`);

        try {
            const page = await this.pdfDoc.getPage(pageNumber);
            const dpr = window.devicePixelRatio || 1;

            // Base unscaled viewport
            const unscaledViewport = page.getViewport({ scale: 1.0 });
            this.originalPageDimensions = {
                width: unscaledViewport.width,
                height: unscaledViewport.height,
            };

            // Scaled viewport for canvas layout
            const viewport = page.getViewport({ scale: zoom });

            // 1. Configure PDF Canvas (Layer 0)
            const pdfCtx = this.pdfCanvas.getContext('2d');
            this.pdfCanvas.width = Math.floor(viewport.width * dpr);
            this.pdfCanvas.height = Math.floor(viewport.height * dpr);
            this.pdfCanvas.style.width = Math.floor(viewport.width) + 'px';
            this.pdfCanvas.style.height = Math.floor(viewport.height) + 'px';

            // 2. Configure Annotation Canvas (Layer 1) to match identical dimensions
            const annotCtx = this.annotationCanvas.getContext('2d');
            this.annotationCanvas.width = Math.floor(viewport.width * dpr);
            this.annotationCanvas.height = Math.floor(viewport.height * dpr);
            this.annotationCanvas.style.width = Math.floor(viewport.width) + 'px';
            this.annotationCanvas.style.height = Math.floor(viewport.height) + 'px';

            // Scale contexts for high-DPI
            pdfCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
            annotCtx.setTransform(dpr, 0, 0, dpr, 0, 0);

            // 3. Render PDF page content
            const renderContext = {
                canvasContext: pdfCtx,
                viewport: viewport,
            };

            this.renderTask = page.render(renderContext);
            await this.renderTask.promise;
            this.renderTask = null;

            this.onPageRendered({
                pageNumber: this.currentPage,
                zoom: this.zoom,
                dpr: dpr,
                renderedWidth: viewport.width,
                renderedHeight: viewport.height,
                originalWidth: this.originalPageDimensions.width,
                originalHeight: this.originalPageDimensions.height,
            });
        } catch (err) {
            if (err.name !== 'RenderingCancelledException') {
                console.error(`Error rendering page ${pageNumber}:`, err);
            }
        } finally {
            this.isRendering = false;
            this.onLoadingChange(false);
        }
    }

    async nextPage() {
        if (this.currentPage < this.totalPages) {
            await this.renderPage(this.currentPage + 1, this.zoom);
        }
    }

    async prevPage() {
        if (this.currentPage > 1) {
            await this.renderPage(this.currentPage - 1, this.zoom);
        }
    }

    async setZoom(newZoom) {
        const clamped = Math.max(0.5, Math.min(3.0, Number(newZoom.toFixed(2))));
        if (clamped !== this.zoom) {
            await this.renderPage(this.currentPage, clamped);
        }
    }
}
