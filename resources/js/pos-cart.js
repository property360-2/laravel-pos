export default function posCart() {
    return {
        products: [],
        categories: [],
        cart: [],
        search: '',
        categoryId: '',
        loadingProducts: true,
        checkoutOpen: false,
        processing: false,
        receiptOpen: false,
        receipt: null,
        discountInput: '0',
        discountIsPercent: false,
        form: {
            payment_method: 'cash',
            amount_paid: '',
        },

        async init() {
            await this.loadCategories();
            await this.loadProducts();
            this.$watch('search', () => this.loadProducts());
        },

        async loadCategories() {
            try {
                const { data } = await axios.get('/api/categories');
                this.categories = data;
            } catch { /* silent */ }
        },

        async loadProducts() {
            this.loadingProducts = true;
            try {
                const params = { per_page: 60 };
                if (this.search) params.search = this.search;
                if (this.categoryId) params.category_id = this.categoryId;
                const { data } = await axios.get('/api/products', { params });
                this.products = data.data;
            } catch {
                this.$store.toast.error('Failed to load products.');
            } finally {
                this.loadingProducts = false;
            }
        },

        get filteredProducts() {
            return this.products;
        },

        get cartCount() {
            return this.cart.reduce((sum, line) => sum + line.quantity, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, line) => sum + line.unit_price * line.quantity, 0);
        },

        get discount() {
            const value = parseFloat(this.discountInput) || 0;
            if (this.discountIsPercent) {
                return Math.min(Math.max((this.subtotal * value) / 100, 0), this.subtotal);
            }
            return Math.min(Math.max(value, 0), this.subtotal);
        },

        get total() {
            return Math.max(this.subtotal - this.discount, 0);
        },

        get change() {
            const paid = parseFloat(this.form.amount_paid) || 0;
            return Math.max(paid - this.total, 0);
        },

        canCheckout() {
            const paid = parseFloat(this.form.amount_paid) || 0;
            return this.cart.length > 0 && paid >= this.total;
        },

        addToCart(product) {
            if (product.stock_quantity <= 0) {
                this.$store.toast.error(`${product.name} is out of stock.`);
                return;
            }

            const existing = this.cart.find((line) => line.product_id === product.id);

            if (existing) {
                if (existing.quantity >= product.stock_quantity) {
                    this.$store.toast.error(`Only ${product.stock_quantity} left for ${product.name}.`);
                    return;
                }
                existing.quantity++;
                return;
            }

            this.cart.push({
                product_id: product.id,
                name: product.name,
                unit_price: Number(product.price),
                stock_quantity: product.stock_quantity,
                quantity: 1,
            });
        },

        increment(line) {
            if (line.quantity >= line.stock_quantity) {
                this.$store.toast.error(`Only ${line.stock_quantity} left for ${line.name}.`);
                return;
            }
            line.quantity++;
        },

        decrement(line) {
            line.quantity--;
            if (line.quantity <= 0) {
                this.removeFromCart(line);
            }
        },

        removeFromCart(line) {
            this.cart = this.cart.filter((item) => item.product_id !== line.product_id);
        },

        clearCart() {
            this.cart = [];
            this.discountInput = '0';
            this.form.amount_paid = '';
            this.form.payment_method = 'cash';
        },

        openCheckout() {
            if (this.cart.length === 0) return;
            if (!this.form.amount_paid && this.total > 0) {
                this.form.amount_paid = this.total.toFixed(2);
            }
            this.checkoutOpen = true;
        },

        quickCash(amount) {
            this.form.amount_paid = amount.toFixed(2);
        },

        async checkout() {
            if (!this.canCheckout() || this.processing) return;

            this.processing = true;

            try {
                const { data } = await axios.post('/api/transactions', {
                    items: this.cart.map((line) => ({
                        product_id: line.product_id,
                        quantity: line.quantity,
                    })),
                    discount: Number(this.discount.toFixed(2)),
                    payment_method: this.form.payment_method,
                    amount_paid: Number(this.form.amount_paid),
                });

                this.receipt = data;
                this.checkoutOpen = false;
                this.receiptOpen = true;
                this.clearCart();
                await this.loadProducts();
            } catch (error) {
                const message = error.response?.data?.errors
                    ? Object.values(error.response.data.errors).flat().join(' ')
                    : error.response?.data?.message || 'Checkout failed. Please try again.';
                this.$store.toast.error(message);
            } finally {
                this.processing = false;
            }
        },

        closeReceipt() {
            this.receiptOpen = false;
            this.receipt = null;
        },
    };
}
