import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.marked = marked;
window.DOMPurify = DOMPurify;

// Configure marked for safe defaults
marked.setOptions({
    breaks: true,
    gfm: true,
});

// Alpine stores
Alpine.store('toast', {
    messages: [],
    add(message, type = 'info', duration = 5000) {
        const id = Date.now();
        this.messages.push({ id, message, type, duration });
        setTimeout(() => this.remove(id), duration);
    },
    remove(id) {
        this.messages = this.messages.filter(m => m.id !== id);
    },
    success(message) { this.add(message, 'success'); },
    error(message) { this.add(message, 'error'); },
    info(message) { this.add(message, 'info'); },
});

Alpine.store('darkMode', {
    on: localStorage.getItem('darkMode') === 'true',
    toggle() {
        this.on = !this.on;
        localStorage.setItem('darkMode', this.on);
        document.documentElement.classList.toggle('dark', this.on);
    },
    init() {
        document.documentElement.classList.toggle('dark', this.on);
    }
});

Alpine.start();
