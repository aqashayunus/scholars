/**
 * Olympia Scholars - Storage & Autosave Manager
 * 
 * Manages dual-layer persistence:
 * 1. Immediate client-side IndexedDB buffer for offline protection and crash resilience.
 * 2. Debounced (1,000ms) background server synchronization with conflict detection.
 */

export class StorageManager {
    constructor(config) {
        this.submissionId = config.submissionId;
        this.savePageUrl = config.savePageUrl; // template or function: (page) => `/student/submissions/${id}/pages/${page}`
        this.csrfToken = config.csrfToken;
        this.onStatusChange = config.onStatusChange || (() => {});

        this.db = null;
        this.dbName = 'OlympiaInteractiveStore';
        this.storeName = 'drafts';
        this.saveTimers = new Map(); // pageNumber -> timeoutId
        this.pendingPages = new Set();
        this.isSyncing = false;

        this._initDb();
        this._setupListeners();
    }

    _initDb() {
        if (!window.indexedDB) {
            console.warn('IndexedDB not supported in this browser. Local offline recovery disabled.');
            return;
        }

        const request = window.indexedDB.open(this.dbName, 1);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(this.storeName)) {
                db.createObjectStore(this.storeName, { keyPath: 'key' });
            }
        };
        request.onsuccess = (e) => {
            this.db = e.target.result;
            this.syncPendingOfflineDrafts();
        };
        request.onerror = (e) => {
            console.error('Failed to open IndexedDB:', e);
        };
    }

    _setupListeners() {
        window.addEventListener('online', () => {
            this.onStatusChange('online', { message: 'Internet reconnected. Syncing drafts...' });
            this.syncPendingOfflineDrafts();
        });

        window.addEventListener('offline', () => {
            this.onStatusChange('offline', { message: 'Working offline. Changes saved locally.' });
        });

        window.addEventListener('beforeunload', (e) => {
            if (this.pendingPages.size > 0 || this.isSyncing) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    }

    /**
     * Persist strokes immediately to IndexedDB and queue debounced server autosave.
     */
    queueSave(pageNumber, strokes, zoom = 1.0, totalPages = 1) {
        this.pendingPages.add(pageNumber);
        this.onStatusChange('saving', { pageNumber });

        const draftRecord = {
            key: `sub_${this.submissionId}_p_${pageNumber}`,
            submissionId: this.submissionId,
            pageNumber: pageNumber,
            strokes: strokes,
            zoom: zoom,
            totalPages: totalPages,
            timestamp: Date.now(),
            synced: false,
        };

        // 1. Immediate IndexedDB write
        this._writeIndexedDb(draftRecord);

        // 2. Clear existing debounce timer for this page
        if (this.saveTimers.has(pageNumber)) {
            clearTimeout(this.saveTimers.get(pageNumber));
        }

        // 3. Set debounced server sync (1,000ms)
        const timer = setTimeout(() => {
            this.saveTimers.delete(pageNumber);
            this._sendPageToServer(pageNumber, strokes, zoom, totalPages);
        }, 1000);

        this.saveTimers.set(pageNumber, timer);
    }

    _writeIndexedDb(record) {
        if (!this.db) return;
        try {
            const tx = this.db.transaction([this.storeName], 'readwrite');
            const store = tx.objectStore(this.storeName);
            store.put(record);
        } catch (err) {
            console.warn('Error saving to IndexedDB:', err);
        }
    }

    async _sendPageToServer(pageNumber, strokes, zoom, totalPages) {
        if (!navigator.onLine) {
            this.onStatusChange('offline', { pageNumber, message: 'Saved to device (Offline)' });
            return;
        }

        const url = typeof this.savePageUrl === 'function' 
            ? this.savePageUrl(pageNumber)
            : this.savePageUrl.replace(':page', pageNumber);

        const payload = {
            annotation_json: JSON.stringify({ version: 1, strokes: strokes }),
            stroke_count: strokes.length,
            current_zoom: zoom,
            total_pages: totalPages,
        };

        this.isSyncing = true;

        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.pendingPages.delete(pageNumber);
                this._markIndexedDbSynced(pageNumber);
                this.onStatusChange('saved', { 
                    pageNumber, 
                    lastSavedAt: data.last_saved_at || 'Just now',
                    pageVersion: data.page_version 
                });
            } else if (response.status === 409) {
                // Conflict
                this.onStatusChange('conflict', { pageNumber, message: 'Server has newer changes.' });
            } else if (response.status === 403) {
                // Locked
                this.onStatusChange('locked', { message: 'Submission is locked.' });
            } else {
                throw new Error(data.message || 'Save failed');
            }
        } catch (err) {
            console.error(`Autosave failed for page ${pageNumber}:`, err);
            this.onStatusChange('error', { pageNumber, message: 'Could not reach server. Preserved on device.' });
        } finally {
            this.isSyncing = false;
        }
    }

    _markIndexedDbSynced(pageNumber) {
        if (!this.db) return;
        try {
            const tx = this.db.transaction([this.storeName], 'readwrite');
            const store = tx.objectStore(this.storeName);
            const key = `sub_${this.submissionId}_p_${pageNumber}`;
            const req = store.get(key);
            req.onsuccess = () => {
                if (req.result) {
                    req.result.synced = true;
                    store.put(req.result);
                }
            };
        } catch (err) {
            console.warn('Error updating IndexedDB status:', err);
        }
    }

    /**
     * Scan IndexedDB for any unsynced pages and sync to server.
     */
    async syncPendingOfflineDrafts() {
        if (!this.db || !navigator.onLine) return;

        try {
            const tx = this.db.transaction([this.storeName], 'readonly');
            const store = tx.objectStore(this.storeName);
            const req = store.getAll();

            req.onsuccess = async () => {
                const records = req.result || [];
                const unsynced = records.filter(r => r.submissionId === this.submissionId && !r.synced);

                for (const item of unsynced) {
                    await this._sendPageToServer(item.pageNumber, item.strokes, item.zoom, item.totalPages);
                }
            };
        } catch (err) {
            console.warn('Error in syncPendingOfflineDrafts:', err);
        }
    }

    /**
     * Load cached offline draft for a page if server data is unavailable.
     */
    async getCachedDraft(pageNumber) {
        if (!this.db) return null;
        return new Promise((resolve) => {
            try {
                const tx = this.db.transaction([this.storeName], 'readonly');
                const store = tx.objectStore(this.storeName);
                const req = store.get(`sub_${this.submissionId}_p_${pageNumber}`);
                req.onsuccess = () => resolve(req.result || null);
                req.onerror = () => resolve(null);
            } catch {
                resolve(null);
            }
        });
    }
}
