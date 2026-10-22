/**
 * Olympia Scholars - Text Engine
 * 
 * Provides interactive text annotation on PDF pages.
 * Supports adding, inline editing, moving, resizing, styling, and deleting text boxes.
 */

export class TextEngine {
    constructor(container, config = {}) {
        this.container = container; // DOM element #pdf-text-layer
        this.isLocked = Boolean(config.isLocked);
        this.strokes = []; // Reference to active page strokes array

        // Dimensions
        this.pdfOriginalWidth = 595;
        this.pdfOriginalHeight = 842;
        this.zoom = 1.0;
        this.renderedWidth = 595;
        this.renderedHeight = 842;

        // Current state
        this.tool = 'pen';
        this.currentColor = config.defaultColor || '#111827';
        this.currentFontSize = config.defaultFontSize || 17;
        this.activeTextId = null;
        this.isDeleting = false;

        // Callbacks
        this.onTextCreated = config.onTextCreated || (() => {});
        this.onTextChanged = config.onTextChanged || (() => {});
        this.onTextMoved = config.onTextMoved || (() => {});
        this.onTextDeleted = config.onTextDeleted || (() => {});

        // Canvas for measuring text dimensions
        this.measureCanvas = document.createElement('canvas');
        this.measureCtx = this.measureCanvas.getContext('2d');

        this._bindGlobalEvents();
    }

    setPageDimensions(origWidth, origHeight, zoom) {
        this.pdfOriginalWidth = origWidth;
        this.pdfOriginalHeight = origHeight;
        this.zoom = zoom;
        this.renderedWidth = origWidth * zoom;
        this.renderedHeight = origHeight * zoom;

        if (this.container) {
            this.container.style.width = Math.floor(this.renderedWidth) + 'px';
            this.container.style.height = Math.floor(this.renderedHeight) + 'px';
        }

        // Reposition and re-scale existing text items in DOM
        this._updateDomPositions();
    }

    setTool(tool) {
        this.tool = tool;
        if (this.container) {
            if (tool === 'text') {
                this.container.style.pointerEvents = 'auto';
                this.container.style.cursor = 'text';
            } else {
                this.container.style.pointerEvents = 'none';
                this.container.style.cursor = 'default';
            }
        }
        if (tool !== 'text') {
            this.commitActiveText();
        }
    }

    setColor(color) {
        this.currentColor = color;
        if (this.activeTextId) {
            const stroke = this.strokes.find(s => s.id === this.activeTextId);
            if (stroke && stroke.color !== color) {
                const old = { ...stroke };
                stroke.color = color;
                const el = this.container.querySelector(`[data-id="${stroke.id}"] textarea`);
                if (el) el.style.color = color;
                this.onTextChanged(stroke, old);
            }
        }
    }

    setFontSize(fontSize) {
        this.currentFontSize = fontSize;
        if (this.activeTextId) {
            const stroke = this.strokes.find(s => s.id === this.activeTextId);
            if (stroke && stroke.fontSize !== fontSize) {
                const old = { ...stroke };
                stroke.fontSize = fontSize;
                const el = this.container.querySelector(`[data-id="${stroke.id}"] textarea`);
                if (el) {
                    el.style.fontSize = `${fontSize * this.zoom}px`;
                    this._autoResizeTextarea(el, stroke);
                }
                this.onTextChanged(stroke, old);
            }
        }
    }

    setTextItems(strokes) {
        this.strokes = strokes || [];
        this.clear();

        const textStrokes = this.strokes.filter(s => s.tool === 'text');
        for (const stroke of textStrokes) {
            this._renderTextElement(stroke);
        }
    }

    createNewText(normX, normY) {
        if (this.isLocked) return;

        this.commitActiveText();

        const stroke = {
            id: 'txt_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
            tool: 'text',
            text: '',
            x: Math.max(0.01, Math.min(0.92, normX)),
            y: Math.max(0.01, Math.min(0.95, normY)),
            color: this.currentColor,
            fontSize: this.currentFontSize,
        };

        this.strokes.push(stroke);
        const itemEl = this._renderTextElement(stroke);

        // Immediately focus textarea
        const textarea = itemEl.querySelector('textarea');
        if (textarea) {
            this.activeTextId = stroke.id;
            this._setActiveState(itemEl, true);
            setTimeout(() => {
                textarea.focus();
            }, 30);
        }

        this.onTextCreated(stroke);
    }

    commitActiveText() {
        if (!this.activeTextId) return;

        const activeId = this.activeTextId;
        const itemEl = this.container.querySelector(`[data-id="${activeId}"]`);
        const stroke = this.strokes.find(s => s.id === activeId);

        this.activeTextId = null;

        if (itemEl) {
            this._setActiveState(itemEl, false);
            const textarea = itemEl.querySelector('textarea');
            if (textarea && stroke) {
                const newText = textarea.value.trim();
                if (newText === '') {
                    this.deleteText(activeId);
                    return;
                }
            }
        }
    }

    deleteText(id) {
        this.isDeleting = true;
        const index = this.strokes.findIndex(s => s.id === id);
        if (index === -1) {
            this.isDeleting = false;
            return;
        }

        const removed = this.strokes.splice(index, 1)[0];
        const el = this.container.querySelector(`[data-id="${id}"]`);
        if (el) {
            el.remove();
        }

        if (this.activeTextId === id) {
            this.activeTextId = null;
        }

        this.onTextDeleted(removed, index);

        setTimeout(() => {
            this.isDeleting = false;
        }, 150);
    }

    clear() {
        if (this.container) {
            this.container.innerHTML = '';
        }
        this.activeTextId = null;
    }

    // ─────────────────────────────────────────────────────────────
    // INTERNAL RENDERING & EVENT HANDLING
    // ─────────────────────────────────────────────────────────────

    _renderTextElement(stroke) {
        if (!this.container) return null;

        const left = Math.round(stroke.x * this.renderedWidth);
        const top = Math.round(stroke.y * this.renderedHeight);
        const scaledFontSize = Math.round((stroke.fontSize || 17) * this.zoom);

        const item = document.createElement('div');
        item.className = 'pdf-text-box absolute pointer-events-auto group rounded-lg transition-all border border-transparent select-text';
        item.dataset.id = stroke.id;
        item.style.left = `${left}px`;
        item.style.top = `${top}px`;
        item.style.zIndex = '30';
        item.style.userSelect = 'text';
        item.style.webkitUserSelect = 'text';

        let controlsHtml = '';
        if (!this.isLocked) {
            controlsHtml = `
                <!-- Corner Drag Handle (Top-Left) -->
                <button type="button" class="pdf-text-drag-handle absolute -top-3.5 -left-3 w-5 h-5 rounded-md bg-white/95 border border-gray-300 text-gray-500 hover:text-gray-800 shadow-xs flex items-center justify-center cursor-grab active:cursor-grabbing transition-all z-50 opacity-0 group-hover:opacity-100" title="Drag to reposition">
                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM7 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM7 16a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM16 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM16 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM16 16a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </button>

                <!-- Corner Delete Button (Top-Right) -->
                <button type="button" class="pdf-text-delete-btn absolute -top-3.5 -right-3 w-5 h-5 rounded-md bg-white/95 border border-gray-300 text-gray-500 hover:text-rose-600 hover:border-gray-400 shadow-xs flex items-center justify-center transition-all cursor-pointer z-50 opacity-0 group-hover:opacity-100" title="Delete text (✕)">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;
        }

        item.innerHTML = `
            ${controlsHtml}
            <textarea
                class="pdf-text-input block resize-none overflow-hidden outline-none transition-colors border border-transparent rounded-md px-2 py-1 leading-snug cursor-text"
                style="font-size: ${scaledFontSize}px; color: ${stroke.color || '#111827'}; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; min-width: 90px; user-select: text !important; -webkit-user-select: text !important; background: transparent;"
                placeholder="${this.isLocked ? '' : 'Type text...'}"
                rows="1"
                spellcheck="false"
                ${this.isLocked ? 'readonly tabindex="-1"' : ''}
            ></textarea>
        `;

        const textarea = item.querySelector('textarea');
        textarea.value = stroke.text || '';

        this.container.appendChild(item);
        this._autoResizeTextarea(textarea, stroke);

        if (!this.isLocked) {
            this._bindTextItemEvents(item, stroke, textarea);
        }

        return item;
    }

    _bindTextItemEvents(item, stroke, textarea) {
        let initialText = stroke.text || '';

        // Prevent events inside text box from bubbling to background layer or canvas
        item.addEventListener('pointerdown', (e) => {
            e.stopPropagation();
        });

        // Clicking anywhere on the text box focuses the textarea immediately
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            if (document.activeElement !== textarea) {
                textarea.focus();
            }
        });

        // Focus & Edit
        textarea.addEventListener('focus', () => {
            this.activeTextId = stroke.id;
            initialText = textarea.value;
            this._setActiveState(item, true);
        });

        // Input & Auto-resize
        textarea.addEventListener('input', () => {
            stroke.text = textarea.value;
            this._autoResizeTextarea(textarea, stroke);
        });

        // Keyboard controls: Delete, Backspace, Escape
        textarea.addEventListener('keydown', (e) => {
            // Delete text box if user presses Delete or Backspace when text is empty
            if (e.key === 'Backspace' && textarea.value.trim() === '') {
                e.preventDefault();
                this.deleteText(stroke.id);
                return;
            }
            // Delete text box if user selects all text and hits Delete
            if (e.key === 'Delete') {
                const allSelected = (textarea.selectionStart === 0 && textarea.selectionEnd === textarea.value.length);
                if (textarea.value.trim() === '' || allSelected) {
                    e.preventDefault();
                    this.deleteText(stroke.id);
                    return;
                }
            }
            // Escape key commits and deselects
            if (e.key === 'Escape') {
                e.preventDefault();
                textarea.blur();
                return;
            }
        });

        // Blur & Commit
        textarea.addEventListener('blur', () => {
            setTimeout(() => {
                if (this.isDeleting) return;

                const currentVal = textarea.value.trim();
                if (currentVal === '') {
                    // Empty text box -> delete automatically
                    this.deleteText(stroke.id);
                    return;
                }

                if (textarea.value !== initialText) {
                    const old = { ...stroke, text: initialText };
                    stroke.text = textarea.value;
                    initialText = textarea.value;
                    this.onTextChanged(stroke, old);
                }

                this._setActiveState(item, false);
                if (this.activeTextId === stroke.id) {
                    this.activeTextId = null;
                }
            }, 120);
        });

        // Delete button click & mousedown
        const deleteBtn = item.querySelector('.pdf-text-delete-btn');
        if (deleteBtn) {
            const handleDelete = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.deleteText(stroke.id);
            };

            deleteBtn.addEventListener('mousedown', handleDelete);
            deleteBtn.addEventListener('pointerdown', handleDelete);
            deleteBtn.addEventListener('click', handleDelete);
        }

        // Drag handle for moving
        const dragHandle = item.querySelector('.pdf-text-drag-handle');
        if (dragHandle) {
            this._bindDrag(dragHandle, item, stroke);
        }
    }

    _bindDrag(dragHandle, item, stroke) {
        let isDragging = false;
        let startPointerX = 0;
        let startPointerY = 0;
        let initialStrokeX = stroke.x;
        let initialStrokeY = stroke.y;
        let beforeState = null;

        const onPointerDown = (e) => {
            e.preventDefault();
            e.stopPropagation();

            isDragging = true;
            startPointerX = e.clientX;
            startPointerY = e.clientY;
            initialStrokeX = stroke.x;
            initialStrokeY = stroke.y;
            beforeState = { ...stroke };

            dragHandle.style.cursor = 'grabbing';
            try {
                dragHandle.setPointerCapture(e.pointerId);
            } catch (_) {}

            window.addEventListener('pointermove', onPointerMove);
            window.addEventListener('pointerup', onPointerUp);
            window.addEventListener('pointercancel', onPointerUp);
        };

        const onPointerMove = (e) => {
            if (!isDragging) return;

            const dx = e.clientX - startPointerX;
            const dy = e.clientY - startPointerY;

            const deltaNormX = dx / this.renderedWidth;
            const deltaNormY = dy / this.renderedHeight;

            // Constrain within document boundaries
            stroke.x = Math.max(0.005, Math.min(0.92, initialStrokeX + deltaNormX));
            stroke.y = Math.max(0.005, Math.min(0.95, initialStrokeY + deltaNormY));

            item.style.left = `${Math.round(stroke.x * this.renderedWidth)}px`;
            item.style.top = `${Math.round(stroke.y * this.renderedHeight)}px`;
        };

        const onPointerUp = (e) => {
            if (!isDragging) return;
            isDragging = false;

            dragHandle.style.cursor = 'grab';
            try {
                dragHandle.releasePointerCapture(e.pointerId);
            } catch (_) {}

            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            window.removeEventListener('pointercancel', onPointerUp);

            if (beforeState && (beforeState.x !== stroke.x || beforeState.y !== stroke.y)) {
                this.onTextMoved(stroke, beforeState);
            }
        };

        dragHandle.addEventListener('pointerdown', onPointerDown);
    }

    _setActiveState(item, active) {
        const deleteBtn = item.querySelector('.pdf-text-delete-btn');
        const dragHandle = item.querySelector('.pdf-text-drag-handle');
        const textarea = item.querySelector('textarea');

        if (active) {
            item.classList.add('active-text-box');
            item.style.zIndex = '50';
            item.style.backgroundColor = 'transparent';
            item.style.borderColor = '#94A3B8';
            item.style.borderStyle = 'dashed';
            item.style.borderWidth = '1px';
            item.style.boxShadow = 'none';

            if (deleteBtn) {
                deleteBtn.classList.remove('opacity-0');
                deleteBtn.classList.add('opacity-100');
            }
            if (dragHandle) {
                dragHandle.classList.remove('opacity-0');
                dragHandle.classList.add('opacity-100');
            }
            if (textarea) {
                textarea.style.backgroundColor = 'transparent';
            }
        } else {
            item.classList.remove('active-text-box');
            item.style.zIndex = '30';
            item.style.backgroundColor = 'transparent';
            item.style.borderColor = 'transparent';
            item.style.borderStyle = 'solid';
            item.style.borderWidth = '1px';
            item.style.boxShadow = 'none';

            if (deleteBtn) {
                deleteBtn.classList.add('opacity-0');
                deleteBtn.classList.remove('opacity-100');
            }
            if (dragHandle) {
                dragHandle.classList.add('opacity-0');
                dragHandle.classList.remove('opacity-100');
            }
        }
    }

    _autoResizeTextarea(textarea, stroke) {
        if (!textarea) return;

        const scaledFontSize = Math.round((stroke.fontSize || 17) * this.zoom);
        const fontStr = `${scaledFontSize}px 'Plus Jakarta Sans', system-ui, sans-serif`;
        this.measureCtx.font = fontStr;

        const lines = (textarea.value || textarea.placeholder || '').split('\n');
        let maxLineWidth = 0;

        for (const line of lines) {
            const metrics = this.measureCtx.measureText(line);
            if (metrics.width > maxLineWidth) {
                maxLineWidth = metrics.width;
            }
        }

        // Add padding & bounds
        const availableWidth = (1 - stroke.x) * this.renderedWidth - 20;
        const targetWidth = Math.max(90 * this.zoom, maxLineWidth + 28);
        const finalWidth = Math.min(targetWidth, availableWidth);

        textarea.style.width = `${Math.ceil(finalWidth)}px`;
        textarea.style.height = 'auto';
        textarea.style.height = `${Math.max(scaledFontSize * 1.4, textarea.scrollHeight)}px`;
    }

    _updateDomPositions() {
        for (const stroke of this.strokes) {
            if (stroke.tool !== 'text') continue;
            const el = this.container.querySelector(`[data-id="${stroke.id}"]`);
            if (el) {
                el.style.left = `${Math.round(stroke.x * this.renderedWidth)}px`;
                el.style.top = `${Math.round(stroke.y * this.renderedHeight)}px`;

                const textarea = el.querySelector('textarea');
                if (textarea) {
                    const scaledFontSize = Math.round((stroke.fontSize || 17) * this.zoom);
                    textarea.style.fontSize = `${scaledFontSize}px`;
                    this._autoResizeTextarea(textarea, stroke);
                }
            }
        }
    }

    _bindGlobalEvents() {
        // Handle clicks on #pdf-text-layer to create new text boxes or select existing ones
        if (!this.isLocked && this.container) {
            this.container.addEventListener('pointerdown', (e) => {
                if (this.isLocked) return;

                // If user clicked inside an existing text box, drag handle, or delete button:
                if (e.target.closest('.pdf-text-box')) {
                    // DO NOT create a new text box! User is interacting with an existing text box!
                    return;
                }

                // If active tool is 'text', clicking empty space on the document creates a new text box:
                if (this.tool === 'text') {
                    if (e.pointerType === 'mouse' && e.button !== 0) return;

                    const rect = this.container.getBoundingClientRect();
                    const normX = Math.max(0.01, Math.min(0.92, (e.clientX - rect.left) / rect.width));
                    const normY = Math.max(0.01, Math.min(0.95, (e.clientY - rect.top) / rect.height));

                    this.createNewText(normX, normY);
                }
            });
        }

        // Deselect text box on Escape key
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeTextId) {
                this.commitActiveText();
            }
        });
    }
}
