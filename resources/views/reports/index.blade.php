<x-layouts.app>
    <x-slot:title>Reports</x-slot>

    <div x-data="reportsPage()" x-init="init()" class="space-y-6">

        <div class="flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Start Date</label>
                    <input type="date" x-model="startDate" @change="loadAll()" class="rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">End Date</label>
                    <input type="date" x-model="endDate" @change="loadAll()" class="rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                </div>
            </div>
            <x-button variant="secondary" @click="setThisMonth()">This Month</x-button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                <p class="mt-1 text-3xl font-bold text-emerald-600" x-text="summary ? Stockflow.money(summary.total_revenue) : '—'"></p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm font-medium text-slate-500">Total Transactions</p>
                <p class="mt-1 text-3xl font-bold text-slate-900" x-text="summary?.total_transactions ?? '—'"></p>
            </div>
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm font-medium text-slate-500">Average Basket Value</p>
                <p class="mt-1 text-3xl font-bold text-indigo-600" x-text="summary ? Stockflow.money(summary.average_basket) : '—'"></p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">emoji_events</span> Best Selling Products</h3>
                <x-table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">#</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Qty</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in bestSelling" :key="item.product_name">
                            <tr>
                                <td class="px-4 py-2 text-slate-400" x-text="index + 1"></td>
                                <td class="px-4 py-2 font-medium text-slate-800" x-text="item.product_name"></td>
                                <td class="px-4 py-2 text-right text-slate-600" x-text="item.total_quantity"></td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-800" x-text="Stockflow.money(item.total_revenue)"></td>
                            </tr>
                        </template>
                    </tbody>
                </x-table>
                <div x-show="bestSelling.length === 0" class="p-6 text-center text-sm text-slate-400">No sales data for this period.</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">notification_important</span> Low Stock Alert</h3>
                <x-table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Category</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="product in lowStockItems" :key="product.id">
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-800" x-text="product.name"></td>
                                <td class="px-4 py-2 text-slate-600" x-text="product.category?.name"></td>
                                <td class="px-4 py-2 text-right">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="product.stock_quantity <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'" x-text="product.stock_quantity"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </x-table>
                <div x-show="lowStockItems.length === 0" class="p-6 text-center text-sm text-slate-400">All products sufficiently stocked.</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">sell</span> Inventory Valuation by Category</h3>
                <x-table>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-500">Category</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Items</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Units</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-slate-500">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in inventoryValue" :key="row.category">
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-800" x-text="row.category"></td>
                                <td class="px-4 py-2 text-right text-slate-600" x-text="row.product_count"></td>
                                <td class="px-4 py-2 text-right text-slate-600" x-text="row.total_stock"></td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-800" x-text="Stockflow.money(row.total_value)"></td>
                            </tr>
                        </template>
                    </tbody>
                </x-table>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">list_alt</span> Stock Movement Ledger</h3>
                <div class="space-y-3">
                    <template x-for="row in stockMovements" :key="row.type">
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                      :class="row.type === 'stock_in' ? 'bg-emerald-100 text-emerald-700' : (row.type === 'sale' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700')"
                                      x-text="row.type.replace('_', ' ')">
                                </span>
                                <span class="text-xs text-slate-500"><span x-text="row.movement_count"></span> entries</span>
                            </div>
                            <span class="font-bold" :class="row.total_quantity > 0 ? 'text-emerald-600' : 'text-red-600'" x-text="(row.total_quantity > 0 ? '+' : '') + row.total_quantity + ' units'"></span>
                        </div>
                    </template>
                    <div x-show="stockMovements.length === 0" class="p-6 text-center text-sm text-slate-400">No movements recorded for this period.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function reportsPage() {
            return {
                summary: null,
                bestSelling: [],
                lowStockItems: [],
                inventoryValue: [],
                stockMovements: [],
                startDate: '',
                endDate: '',

                init() {
                    this.setThisMonth();
                    this.loadAll();
                },

                setThisMonth() {
                    const now = new Date();
                    this.startDate = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
                    this.endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
                },

                dateParams() {
                    const params = {};
                    if (this.startDate) params.start_date = this.startDate;
                    if (this.endDate) params.end_date = this.endDate;
                    return params;
                },

                async loadAll() {
                    try {
                        const [summary, best, low, value, movements] = await Promise.all([
                            axios.get('/api/reports/sales-summary', { params: this.dateParams() }),
                            axios.get('/api/reports/best-selling', { params: { ...this.dateParams(), limit: 10 } }),
                            axios.get('/api/reports/low-stock'),
                            axios.get('/api/reports/inventory-value'),
                            axios.get('/api/reports/stock-movement', { params: this.dateParams() }),
                        ]);
                        this.summary = summary.data;
                        this.bestSelling = best.data;
                        this.lowStockItems = low.data;
                        this.inventoryValue = value.data;
                        this.stockMovements = movements.data;
                    } catch {
                        this.$store.toast.error('Failed to load reports.');
                    }
                },
            };
        }
    </script>
</x-layouts.app>
