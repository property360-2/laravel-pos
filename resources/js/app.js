import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import posCart from './pos-cart';

window.Alpine = Alpine;
window.Chart = Chart;

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],
        counter: 0,
        add(message, type = 'success') {
            const toast = { id: ++this.counter, message, type, visible: true };
            this.items.push(toast);
            setTimeout(() => { toast.visible = false; }, 3500);
            setTimeout(() => {
                this.items = this.items.filter((item) => item.id !== toast.id);
            }, 4000);
        },
        success(message) { this.add(message, 'success'); },
        error(message) { this.add(message, 'error'); },
    });

    Alpine.data('posCart', posCart);
});

window.Stockflow = {
    currency: document.querySelector('meta[name="currency-symbol"]')?.content || '₱',

    money(value) {
        const number = Number(value ?? 0);
        return this.currency + number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatDate(value) {
        if (!value) return '';
        return new Date(value).toLocaleString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
        });
    },
};

Alpine.start();
