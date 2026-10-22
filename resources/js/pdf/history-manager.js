/**
 * Olympia Scholars - History Manager
 * 
 * Manages per-page Undo and Redo stacks for vector drawing actions.
 */

export class HistoryManager {
    constructor(maxHistory = 50) {
        this.maxHistory = maxHistory;
        this.undoStacks = new Map(); // pageNumber -> Array<Action>
        this.redoStacks = new Map(); // pageNumber -> Array<Action>
    }

    _getStack(map, pageNumber) {
        if (!map.has(pageNumber)) {
            map.set(pageNumber, []);
        }
        return map.get(pageNumber);
    }

    pushAction(pageNumber, action) {
        const undoStack = this._getStack(this.undoStacks, pageNumber);
        undoStack.push(action);
        if (undoStack.length > this.maxHistory) {
            undoStack.shift();
        }
        // Clear redo stack on new user action
        this._getStack(this.redoStacks, pageNumber).length = 0;
    }

    canUndo(pageNumber) {
        const undoStack = this._getStack(this.undoStacks, pageNumber);
        return undoStack.length > 0;
    }

    canRedo(pageNumber) {
        const redoStack = this._getStack(this.redoStacks, pageNumber);
        return redoStack.length > 0;
    }

    undo(pageNumber, strokesList) {
        if (!this.canUndo(pageNumber)) return false;

        const undoStack = this._getStack(this.undoStacks, pageNumber);
        const redoStack = this._getStack(this.redoStacks, pageNumber);
        const action = undoStack.pop();

        if (action.type === 'add_stroke') {
            const index = strokesList.findIndex(s => s.id === action.stroke.id);
            if (index !== -1) {
                strokesList.splice(index, 1);
            }
            redoStack.push(action);
            return true;
        }

        if (action.type === 'remove_stroke') {
            const insertIdx = Math.min(action.index ?? strokesList.length, strokesList.length);
            strokesList.splice(insertIdx, 0, action.stroke);
            redoStack.push(action);
            return true;
        }

        if (action.type === 'modify_stroke') {
            const index = strokesList.findIndex(s => s.id === action.before.id);
            if (index !== -1) {
                strokesList[index] = { ...action.before };
            }
            redoStack.push(action);
            return true;
        }

        if (action.type === 'clear_page') {
            strokesList.length = 0;
            strokesList.push(...action.strokes);
            redoStack.push(action);
            return true;
        }

        return false;
    }

    redo(pageNumber, strokesList) {
        if (!this.canRedo(pageNumber)) return false;

        const undoStack = this._getStack(this.undoStacks, pageNumber);
        const redoStack = this._getStack(this.redoStacks, pageNumber);
        const action = redoStack.pop();

        if (action.type === 'add_stroke') {
            strokesList.push(action.stroke);
            undoStack.push(action);
            return true;
        }

        if (action.type === 'remove_stroke') {
            const index = strokesList.findIndex(s => s.id === action.stroke.id);
            if (index !== -1) {
                strokesList.splice(index, 1);
            }
            undoStack.push(action);
            return true;
        }

        if (action.type === 'modify_stroke') {
            const index = strokesList.findIndex(s => s.id === action.after.id);
            if (index !== -1) {
                strokesList[index] = { ...action.after };
            }
            undoStack.push(action);
            return true;
        }

        if (action.type === 'clear_page') {
            strokesList.length = 0;
            undoStack.push(action);
            return true;
        }

        return false;
    }

    clearPageHistory(pageNumber) {
        if (this.undoStacks.has(pageNumber)) this.undoStacks.get(pageNumber).length = 0;
        if (this.redoStacks.has(pageNumber)) this.redoStacks.get(pageNumber).length = 0;
    }
}
