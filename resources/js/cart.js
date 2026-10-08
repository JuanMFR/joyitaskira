/**
 * Carrito de la tienda. Vive en el navegador (localStorage) y al final
 * arma un mensaje para mandar por WhatsApp al vendedor.
 * Cada pieza es única: hay una sola unidad de cada producto.
 *
 * config (window.KIRA): { whatsapp, greeting, cartDataUrl }
 */
const STORAGE_KEY = 'kira-cart-v1';

const moneyFormats = [0, 2].map(
    (digits) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: digits, maximumFractionDigits: digits }),
);

/** Igual que App\Support\Money: $ 12.500 o $ 12.500,50 */
export const formatMoney = (value) => {
    const rounded = Math.round(value * 100) / 100;
    return moneyFormats[Number.isInteger(rounded) ? 0 : 1].format(rounded).replace(/ /g, ' ');
};

function load() {
    try {
        const data = JSON.parse(localStorage.getItem(STORAGE_KEY));
        // Los carritos viejos guardaban cantidad; ya no se usa.
        return Array.isArray(data) ? data.map(({ qty, ...item }) => item) : [];
    } catch {
        return [];
    }
}

export default function cart(config) {
    return {
        items: load(), // [{ id, name, price, priceLabel, image, url, purchasable, statusLabel }]
        open: false,
        toast: null,
        customerName: '',
        note: '',
        whatsapp: config.whatsapp || '',

        init() {
            this.refresh();
            // Mantiene sincronizadas varias pestañas abiertas.
            window.addEventListener('storage', (e) => {
                if (e.key === STORAGE_KEY) this.items = load();
            });
        },

        save() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
            } catch {
                // Sin localStorage (modo privado estricto): el carrito dura hasta recargar.
            }
        },

        get count() {
            return this.items.length;
        },

        get available() {
            return this.items.filter((item) => item.purchasable);
        },

        get total() {
            return this.available.reduce((sum, item) => sum + item.price, 0);
        },

        get totalLabel() {
            return formatMoney(this.total);
        },

        has(id) {
            return this.items.some((item) => item.id === id);
        },

        add(product) {
            if (!product.purchasable || this.has(product.id)) return;

            this.items.push({ ...product });
            this.save();
            this.notify(`${product.name} se agregó al carrito`);
        },

        /** Agrega o quita el producto (botón tipo interruptor). */
        toggle(product) {
            if (this.has(product.id)) {
                this.remove(product.id);
                this.notify(`${product.name} se quitó del carrito`);
            } else {
                this.add(product);
            }
        },

        remove(id) {
            this.items = this.items.filter((item) => item.id !== id);
            this.save();
        },

        clear() {
            this.items = [];
            this.save();
        },

        notify(message) {
            this.toast = message;
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => (this.toast = null), 2600);
        },

        openCart() {
            this.open = true;
            this.refresh();
        },

        /** Actualiza precio, foto y stock desde el servidor; quita los productos dados de baja. */
        async refresh() {
            if (!this.items.length || !config.cartDataUrl) return;

            try {
                const ids = this.items.map((item) => item.id).join(',');
                const response = await fetch(`${config.cartDataUrl}?ids=${ids}`, {
                    headers: { Accept: 'application/json' },
                });
                if (!response.ok) return;

                const fresh = new Map((await response.json()).map((p) => [p.id, p]));
                this.items = this.items
                    .filter((item) => fresh.has(item.id))
                    .map((item) => ({ ...item, ...fresh.get(item.id) }));
                this.save();
            } catch {
                // Sin conexión: se usa lo guardado.
            }
        },

        get message() {
            const lines = [config.greeting || '¡Hola! Quiero consultar por estos productos:', ''];

            this.available.forEach((item) => {
                lines.push(`• ${item.name} — ${item.priceLabel}`);
                lines.push(`  ${item.url}`);
            });

            lines.push('', `*Total: ${this.totalLabel}*`);

            if (this.customerName.trim()) lines.push('', `Mi nombre: ${this.customerName.trim()}`);
            if (this.note.trim()) lines.push(`Comentario: ${this.note.trim()}`);

            return lines.join('\n');
        },

        get whatsappUrl() {
            return `https://wa.me/${this.whatsapp}?text=${encodeURIComponent(this.message)}`;
        },

        /** Consulta directa por un solo producto, sin pasar por el carrito. */
        productQuestionUrl(product) {
            const text = `¡Hola Kira! Quiero consultar por ${product.name} (${product.priceLabel}).\n${product.url}`;
            return `https://wa.me/${this.whatsapp}?text=${encodeURIComponent(text)}`;
        },
    };
}
