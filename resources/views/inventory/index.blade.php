<x-layouts.app>
    <x-slot:title>Inventory</x-slot>

    <div x-data="inventoryPage()" x-init="init()" class="space-y-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-1 rounded-lg bg-white p-1 shadow-sm ring-1 ring-slate-200">
                <button
                    @click="tab = 'stock'"
                    class="rounded-md px-4 py-2 text-sm font-medium transition"
                    :class="tab === 'stock' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >Current Stock</button>
                <button
                    @click="tab = 'movements'; loadMovements()"
                    class="rounded-md px-4 py-2 text-sm font-medium transition"
                    :class="tab === 'movements' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                >Stock Movements</button>
            </div>

            <div x-show="tab === 'stock'" class="flex flex-1 flex-col gap-3 sm:flex-row sm:justify-end">
                <div class="relative sm:w-64">
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search products..."
                        class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-4 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600"
                    />
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" x-model="lowStockOnly" @change="loadStock()" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    Low stock only
                </label>
            </div>
        </div>

        <div x-show="tab === 'stock'">
            <x-table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Threshold</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="product in products" :key="product.id">
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800" x-text="product.name"></p>
                                <p class="text-xs text-slate-400" x-text="product.sku"></p>
                            </td>
                            <td class="px-4 py-3 text-slate-600" x-text="product.category?.name"></td>
                            <td class="px-4 py-3 text-right font-bold"
                                :class="product.stock_quantity <= 0 ? 'text-red-600' : (product.stock_quantity <= product.low_stock_threshold ? 'text-amber-600' : 'text-slate-800')"
                                x-text="product.stock_quantity"></td>
                            <td class="px-4 py-3 text-right text-slate-500" x-text="product.low_stock_threshold"></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                      :class="product.stock_status === 'in_stock' ? 'bg-emerald-100 text-emerald-700' : (product.stock_status === 'low_stock' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')"
                                      x-text="product.stock_status === 'in_stock' ? 'In Stock' : (product.stock_status === 'low_stock' ? 'Low Stock' : 'Out of Stock')">
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <x-button size="sm" @click="openStockIn(product)">Stock In</x-button>
                                    <x-button size="sm" variant="secondary" @click="openAdjust(product)">Adjust</x-button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </x-table>

            <div x-show="!loadingStock && products.length === 0" class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
                <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">inventory_2</span></span>
                <p class="text-sm">No products match this filter.</p>
            </div>
        </div>

        <div x-show="tab === 'movements'" x-cloak>
            <div class="mb-3 flex flex-wrap items-center gap-3">
                <select x-model="movementType" @change="movementPage = 1; loadMovements()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Types</option>
                    <option value="stock_in">Stock In</option>
                    <option value="sale">Sale</option>
                    <option value="adjustment">Adjustment</option>
                </select>
                <input type="date" x-model="startDate" @change="loadMovements()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                <input type="date" x-model="endDate" @change="loadMovements()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
            </div>

            <x-table>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="movement in movements" :key="movement.id">
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500" x-text="Stockflow.formatDate(movement.created_at)"></td>
                            <td class="px-4 py-3 font-medium text-slate-800" x-text="movement.product?.name"></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                      :class="movement.type === 'stock_in' ? 'bg-emerald-100 text-emerald-700' : (movement.type === 'sale' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700')"
                                      x-text="movement.type.replace('_', ' ')">
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold" :class="movement.quantity > 0 ? 'text-emerald-600' : 'text-red-600'" x-text="(movement.quantity > 0 ? '+' : '') + movement.quantity"></td>
                            <td class="px-4 py-3 text-slate-600" x-text="movement.reason ?? '—'"></td>
                            <td class="px-4 py-3 text-slate-600" x-text="movement.user?.name ?? 'System'"></td>
                        </tr>
                    </template>
                </tbody>
            </x-table>

            <div x-show="!loadingMovements && movements.length === 0" class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
                <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">list_alt</span></span>
                <p class="text-sm">No stock movements recorded.</p>
            </div>

            <div class="mt-4 flex items-center justify-between" x-show="movementLastPage > 1">
                <p class="text-xs text-slate-500">Page <span x-text="movementPage"></span> of <span x-text="movementLastPage"></span></p>
                <div class="flex gap-2">
                    <x-button size="sm" variant="secondary" x-bind:disabled="movementPage <= 1" @click="movementPage--; loadMovements()">← Previous</x-button>
                    <x-button size="sm" variant="secondary" x-bind:disabled="movementPage >= movementLastPage" @click="movementPage++; loadMovements()">Next →</x-button>
                </div>
            </div>
        </div>

        <x-modal show="stockInOpen" maxWidth="max-w-md">
            <form @submit.prevent="submitStockIn()" class="p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900"><span class="material-icons align-middle" aria-hidden="true">download</span> Stock In</h3>
                <p class="mb-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                    <span x-text="activeProduct?.name"></span> — current stock: <strong x-text="activeProduct?.stock_quantity"></strong>
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Quantity (+)</label>
                        <input type="number" min="1" x-model="stockForm.quantity" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        <p class="mt-1 text-xs text-red-600" x-show="errors.quantity" x-text="errors.quantity?.[0]"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Reason / Supplier</label>
                        <input type="text" x-model="stockForm.reason" placeholder="e.g. Delivery from supplier" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" @click="stockInOpen = false">Cancel</x-button>
                    <x-button type="submit" x-bind:disabled="saving" x-bind:class="{ 'is-loading': saving }">Confirm Stock In</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal show="adjustOpen" maxWidth="max-w-md">
            <form @submit.prevent="submitAdjust()" class="p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900"><span class="material-icons align-middle" aria-hidden="true">balance</span> Adjust Stock</h3>
                <p class="mb-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                    <span x-text="activeProduct?.name"></span> — current stock: <strong x-text="activeProduct?.stock_quantity"></strong>
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Adjustment Quantity (use − for deductions)</label>
                        <input type="number" x-model="adjustForm.quantity" placeholder="e.g. -2 or 3" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        <p class="mt-1 text-xs text-red-600" x-show="errors.quantity" x-text="errors.quantity?.[0]"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Reason *</label>
                        <input type="text" x-model="adjustForm.reason" placeholder="e.g. Damaged, Expired, Audit" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        <p class="mt-1 text-xs text-red-600" x-show="errors.reason" x-text="errors.reason?.[0]"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" @click="adjustOpen = false">Cancel</x-button>
                    <x-button type="submit" variant="warning" x-bind:disabled="saving" x-bind:class="{ 'is-loading': saving }">Apply Adjustment</x-button>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        function inventoryPage() {
            return {
                tab: 'stock',
                products: [],
                movements: [],
                search: '',
                lowStockOnly: false,
                movementType: '',
                startDate: '',
                endDate: '',
                movementPage: 1,
                movementLastPage: 1,
                loadingStock: true,
                loadingMovements: false,
                saving: false,
                stockInOpen: false,
                adjustOpen: false,
                activeProduct: null,
                errors: {},
                stockForm: { quantity: 1, reason: '' },
                adjustForm: { quantity: '', reason: '' },

                async init() {
                    await this.loadStock();
                    this.$watch('search', () => this.loadStock());
                },

                async loadStock() {
                    this.loadingStock = true;
                    try {
                        const params = {};
                        if (this.search) params.search = this.search;
                        if (this.lowStockOnly) params.low_stock_only = true;
                        const { data } = await axios.get('/api/inventory', { params });
                        this.products = data;
                    } catch {
                        this.$store.toast.error('Failed to load inventory.');
                    } finally {
                        this.loadingStock = false;
                    }
                },

                async loadMovements() {
                    this.loadingMovements = true;
                    try {
                        const params = { page: this.movementPage, per_page: 20 };
                        if (this.movementType) params.type = this.movementType;
                        if (this.startDate) params.start_date = this.startDate;
                        if (this.endDate) params.end_date = this.endDate;
                        const { data } = await axios.get('/api/inventory/movements', { params });
                        this.movements = data.data;
                        this.movementLastPage = data.last_page;
                    } catch {
                        this.$store.toast.error('Failed to load stock movements.');
                    } finally {
                        this.loadingMovements = false;
                    }
                },

                openStockIn(product) {
                    this.activeProduct = product;
                    this.stockForm = { quantity: 1, reason: '' };
                    this.errors = {};
                    this.stockInOpen = true;
                },

                openAdjust(product) {
                    this.activeProduct = product;
                    this.adjustForm = { quantity: '', reason: '' };
                    this.errors = {};
                    this.adjustOpen = true;
                },

                async submitStockIn() {
                    this.saving = true;
                    this.errors = {};
                    try {
                        await axios.post('/api/inventory/stock-in', {
                            product_id: this.activeProduct.id,
                            quantity: Number(this.stockForm.quantity),
                            reason: this.stockForm.reason || null,
                        });
                        this.$store.toast.success('Stock added successfully.');
                        this.stockInOpen = false;
                        await this.loadStock();
                    } catch (error) {
                        this.errors = error.response?.data?.errors ?? {};
                        this.$store.toast.error(error.response?.data?.message ?? 'Stock-in failed.');
                    } finally {
                        this.saving = false;
                    }
                },

                async submitAdjust() {
                    this.saving = true;
                    this.errors = {};
                    try {
                        await axios.post('/api/inventory/adjust', {
                            product_id: this.activeProduct.id,
                            quantity: Number(this.adjustForm.quantity),
                            reason: this.adjustForm.reason,
                        });
                        this.$store.toast.success('Stock adjusted successfully.');
                        this.adjustOpen = false;
                        await this.loadStock();
                    } catch (error) {
                        this.errors = error.response?.data?.errors ?? {};
                        this.$store.toast.error(error.response?.data?.message ?? 'Adjustment failed.');
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
</x-layouts.app>
