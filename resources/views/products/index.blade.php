<x-layouts.app>
    <x-slot:title>Products</x-slot>

    <div x-data="productsPage()" x-init="init()" class="space-y-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row">
                <div class="relative flex-1 sm:max-w-xs">
                    <input
                        type="text"
                        x-model.debounce.300ms="search"
                        placeholder="Search by name or SKU..."
                        class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-4 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600"
                    />
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                </div>
                <select x-model="categoryId" @change="load()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Categories</option>
                    <template x-for="category in categories" :key="category.id">
                        <option :value="category.id" x-text="category.name"></option>
                    </template>
                </select>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" x-model="lowStockOnly" @change="load()" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                    Low stock only
                </label>
            </div>
            <x-button @click="openCreate()">+ Add Product</x-button>
        </div>

        <x-table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Stock</th>
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
                        <td class="px-4 py-3 text-right font-medium text-slate-800" x-text="Stockflow.money(product.price)"></td>
                        <td class="px-4 py-3 text-right" x-text="product.stock_quantity"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  :class="product.stock_status === 'in_stock' ? 'bg-emerald-100 text-emerald-700' : (product.stock_status === 'low_stock' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')"
                                  x-text="product.stock_status === 'in_stock' ? 'In Stock' : (product.stock_status === 'low_stock' ? 'Low Stock' : 'Out of Stock')">
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <x-button size="sm" variant="secondary" @click="openEdit(product)">Edit</x-button>
                                <x-button size="sm" variant="danger" @click="confirmDelete(product)">Delete</x-button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </x-table>

        <div x-show="!loading && products.length === 0" class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
            <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">inventory_2</span></span>
            <p class="text-sm">No products found.</p>
            <x-button size="sm" @click="openCreate()">+ Add your first product</x-button>
        </div>

        <div class="flex items-center justify-between" x-show="lastPage > 1">
            <p class="text-xs text-slate-500">Page <span x-text="page"></span> of <span x-text="lastPage"></span></p>
            <div class="flex gap-2">
                <x-button size="sm" variant="secondary" x-bind:disabled="page <= 1" @click="page--; load()">← Previous</x-button>
                <x-button size="sm" variant="secondary" x-bind:disabled="page >= lastPage" @click="page++; load()">Next →</x-button>
            </div>
        </div>

        <x-modal show="formOpen" maxWidth="max-w-lg">
            <form @submit.prevent="save()" class="p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900" x-text="editingId ? 'Edit Product' : 'Create Product'"></h3>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                        <input type="text" x-model="form.name" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        <p class="mt-1 text-xs text-red-600" x-show="errors.name" x-text="errors.name?.[0]"></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">SKU</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="form.sku" class="block w-full rounded-lg border-0 px-3 py-2 text-sm uppercase ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                            <x-button type="button" variant="secondary" @click="generateSku()">Auto</x-button>
                        </div>
                        <p class="mt-1 text-xs text-red-600" x-show="errors.sku" x-text="errors.sku?.[0]"></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                        <select x-model="form.category_id" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                            <option value="">Select category...</option>
                            <template x-for="category in categories" :key="category.id">
                                <option :value="category.id" x-text="category.name"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-red-600" x-show="errors.category_id" x-text="errors.category_id?.[0]"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Price</label>
                            <input type="number" min="0" step="0.01" x-model="form.price" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                            <p class="mt-1 text-xs text-red-600" x-show="errors.price" x-text="errors.price?.[0]"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Stock Quantity</label>
                            <input type="number" min="0" x-model="form.stock_quantity" :disabled="editingId !== null" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 disabled:bg-slate-50 disabled:text-slate-400" />
                            <p class="mt-1 text-xs text-red-600" x-show="errors.stock_quantity" x-text="errors.stock_quantity?.[0]"></p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Low Stock Threshold</label>
                        <input type="number" min="0" x-model="form.low_stock_threshold" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        <p class="mt-1 text-xs text-red-600" x-show="errors.low_stock_threshold" x-text="errors.low_stock_threshold?.[0]"></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" @click="formOpen = false">Cancel</x-button>
                    <x-button type="submit" x-bind:disabled="saving" x-bind:class="{ 'is-loading': saving }" x-text="editingId ? 'Save Changes' : 'Create Product'">Save</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal show="deleteOpen" maxWidth="max-w-md">
            <div class="p-5 sm:p-6">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Delete Product</h3>
                <p class="mb-5 text-sm text-slate-500">
                    Are you sure you want to delete <strong class="text-slate-700" x-text="deleteTarget?.name"></strong>?
                    It will be hidden from the catalog. Historical transactions are preserved.
                </p>
                <div class="flex justify-end gap-3">
                    <x-button variant="secondary" @click="deleteOpen = false">Cancel</x-button>
                    <x-button variant="danger" x-bind:disabled="deleting" x-bind:class="{ 'is-loading': deleting }" @click="destroy()">Delete</x-button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        function productsPage() {
            return {
                products: [],
                categories: [],
                search: '',
                categoryId: '',
                lowStockOnly: false,
                page: 1,
                lastPage: 1,
                loading: true,
                saving: false,
                deleting: false,
                formOpen: false,
                deleteOpen: false,
                editingId: null,
                deleteTarget: null,
                errors: {},
                form: {},

                emptyForm() {
                    return { name: '', sku: '', category_id: '', price: '', stock_quantity: 0, low_stock_threshold: 5 };
                },

                async init() {
                    this.form = this.emptyForm();
                    const { data } = await axios.get('/api/categories');
                    this.categories = data;
                    await this.load();
                    this.$watch('search', () => { this.page = 1; this.load(); });
                },

                async load() {
                    this.loading = true;
                    try {
                        const params = { page: this.page, per_page: 15 };
                        if (this.search) params.search = this.search;
                        if (this.categoryId) params.category_id = this.categoryId;
                        if (this.lowStockOnly) params.low_stock = true;
                        const { data } = await axios.get('/api/products', { params });
                        this.products = data.data;
                        this.lastPage = data.last_page;
                    } catch {
                        this.$store.toast.error('Failed to load products.');
                    } finally {
                        this.loading = false;
                    }
                },

                openCreate() {
                    this.editingId = null;
                    this.form = this.emptyForm();
                    this.errors = {};
                    this.formOpen = true;
                },

                openEdit(product) {
                    this.editingId = product.id;
                    this.form = {
                        name: product.name,
                        sku: product.sku,
                        category_id: product.category_id,
                        price: product.price,
                        stock_quantity: product.stock_quantity,
                        low_stock_threshold: product.low_stock_threshold,
                    };
                    this.errors = {};
                    this.formOpen = true;
                },

                generateSku() {
                    const category = this.categories.find((c) => String(c.id) === String(this.form.category_id));
                    const prefix = (category?.name ?? 'PRD').slice(0, 3).toUpperCase();
                    this.form.sku = `${prefix}-${String(Date.now()).slice(-6)}`;
                },

                async save() {
                    this.saving = true;
                    this.errors = {};
                    try {
                        const payload = { ...this.form, category_id: Number(this.form.category_id) };
                        if (this.editingId) {
                            delete payload.stock_quantity;
                            await axios.put(`/api/products/${this.editingId}`, payload);
                            this.$store.toast.success('Product updated successfully.');
                        } else {
                            await axios.post('/api/products', payload);
                            this.$store.toast.success('Product created successfully.');
                        }
                        this.formOpen = false;
                        await this.load();
                    } catch (error) {
                        this.errors = error.response?.data?.errors ?? {};
                        this.$store.toast.error('Please fix the form errors.');
                    } finally {
                        this.saving = false;
                    }
                },

                confirmDelete(product) {
                    this.deleteTarget = product;
                    this.deleteOpen = true;
                },

                async destroy() {
                    this.deleting = true;
                    try {
                        await axios.delete(`/api/products/${this.deleteTarget.id}`);
                        this.$store.toast.success('Product deleted successfully.');
                        this.deleteOpen = false;
                        await this.load();
                    } catch (error) {
                        this.$store.toast.error(error.response?.data?.message ?? 'Failed to delete product.');
                    } finally {
                        this.deleting = false;
                    }
                },
            };
        }
    </script>
</x-layouts.app>
