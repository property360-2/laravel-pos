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
            <div class="flex items-center gap-3">
                <x-button variant="secondary" @click="setThisMonth()">This Month</x-button>
                <div class="flex gap-1 rounded-lg bg-slate-100 p-1">
                    <button
                        @click="tab = 'tables'"
                        class="rounded-md px-4 py-2 text-sm font-medium transition"
                        :class="tab === 'tables' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    >
                        <span class="material-icons align-middle text-base leading-none" aria-hidden="true">table_rows</span>
                        Tables
                    </button>
                    <button
                        @click="tab = 'charts'; renderCharts()"
                        class="rounded-md px-4 py-2 text-sm font-medium transition"
                        :class="tab === 'charts' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    >
                        <span class="material-icons align-middle text-base leading-none" aria-hidden="true">bar_chart</span>
                        Charts
                    </button>
                </div>
            </div>
        </div>

        <div x-show="tab === 'tables'" x-cloak class="space-y-6">
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

        <div x-show="tab === 'charts'" x-cloak>
            <div x-show="!hasChartData()" class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
                <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">insights</span></span>
                <p class="text-sm">No sales data for this period.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">show_chart</span> Sales Trend</h3>
                    <div class="relative h-72">
                        <canvas id="chart-sales-trend"></canvas>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">donut_small</span> Revenue by Category</h3>
                    <div class="relative h-72">
                        <canvas id="chart-sales-category"></canvas>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">emoji_events</span> Best Selling Products</h3>
                    <div class="relative h-72">
                        <canvas id="chart-best-selling"></canvas>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">donut_large</span> Inventory Value by Category</h3>
                    <div class="relative h-72">
                        <canvas id="chart-inventory-value"></canvas>
                    </div>
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
                salesTrend: [],
                salesByCategory: [],
                startDate: '',
                endDate: '',
                tab: 'tables',
                chartInstances: {},

                palette: ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'],

                init() {
                    this.setThisMonth();
                    this.loadAll();
                },

                setThisMonth() {
                    const now = new Date();
                    const format = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                    this.startDate = format(new Date(now.getFullYear(), now.getMonth(), 1));
                    this.endDate = format(new Date(now.getFullYear(), now.getMonth() + 1, 0));
                },

                dateParams() {
                    const params = {};
                    if (this.startDate) params.start_date = this.startDate;
                    if (this.endDate) params.end_date = this.endDate;
                    return params;
                },

                async loadAll() {
                    try {
                        const [summary, best, low, value, movements, trend, byCategory] = await Promise.all([
                            axios.get('/api/reports/sales-summary', { params: this.dateParams() }),
                            axios.get('/api/reports/best-selling', { params: { ...this.dateParams(), limit: 10 } }),
                            axios.get('/api/reports/low-stock'),
                            axios.get('/api/reports/inventory-value'),
                            axios.get('/api/reports/stock-movement', { params: this.dateParams() }),
                            axios.get('/api/reports/sales-trend', { params: this.dateParams() }),
                            axios.get('/api/reports/sales-by-category', { params: this.dateParams() }),
                        ]);
                        this.summary = summary.data;
                        this.bestSelling = best.data;
                        this.lowStockItems = low.data;
                        this.inventoryValue = value.data;
                        this.stockMovements = movements.data;
                        this.salesTrend = trend.data;
                        this.salesByCategory = byCategory.data;
                        if (this.tab === 'charts') this.renderCharts();
                    } catch {
                        this.$store.toast.error('Failed to load reports.');
                    }
                },

                hasChartData() {
                    return (this.summary?.total_transactions ?? 0) > 0;
                },

                shortDate(value) {
                    return new Date(value + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
                },

                moneyLabel(value, suffix = '') {
                    return Stockflow.money(value) + (suffix ? ' ' + suffix : '');
                },

                renderCharts() {
                    if (!window.Chart) return;

                    this.$nextTick(() => {
                        Object.values(this.chartInstances).forEach((chart) => chart.destroy());
                        this.chartInstances = {};

                        this.renderSalesTrend();
                        this.renderSalesCategory();
                        this.renderBestSelling();
                        this.renderInventoryValue();
                    });
                },

                renderSalesTrend() {
                    const el = document.getElementById('chart-sales-trend');
                    if (!el) return;

                    this.chartInstances.trend = new Chart(el, {
                        type: 'line',
                        data: {
                            labels: this.salesTrend.map((d) => this.shortDate(d.date)),
                            datasets: [{
                                label: 'Revenue',
                                data: this.salesTrend.map((d) => d.revenue),
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.10)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => this.moneyLabel(ctx.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: {
                                    beginAtZero: true,
                                    ticks: { callback: (value) => Stockflow.currency + Number(value).toLocaleString('en-PH', { maximumFractionDigits: 0 }) },
                                },
                            },
                        },
                    });
                },

                renderSalesCategory() {
                    const el = document.getElementById('chart-sales-category');
                    if (!el) return;

                    const data = this.salesByCategory;
                    this.chartInstances.salesCategory = new Chart(el, {
                        type: 'doughnut',
                        data: {
                            labels: data.map((row) => row.category),
                            datasets: [{
                                data: data.map((row) => row.revenue),
                                backgroundColor: data.map((_, i) => this.palette[i % this.palette.length]),
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => this.moneyLabel(ctx.parsed, `(${this.pctShare(ctx.parsed, data)})`),
                                    },
                                },
                            },
                        },
                    });
                },

                renderBestSelling() {
                    const el = document.getElementById('chart-best-selling');
                    if (!el) return;

                    const data = this.bestSelling.slice(0, 8);
                    this.chartInstances.bestSelling = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: data.map((item) => item.product_name),
                            datasets: [{
                                label: 'Units sold',
                                data: data.map((item) => item.total_quantity),
                                backgroundColor: '#818cf8',
                                borderRadius: 4,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${ctx.parsed.x} sold`,
                                    },
                                },
                            },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0 } },
                                y: { grid: { display: false } },
                            },
                        },
                    });
                },

                renderInventoryValue() {
                    const el = document.getElementById('chart-inventory-value');
                    if (!el) return;

                    const data = this.inventoryValue;
                    this.chartInstances.inventoryValue = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: data.map((row) => row.category),
                            datasets: [{
                                label: 'Inventory value',
                                data: data.map((row) => row.total_value),
                                backgroundColor: data.map((_, i) => this.palette[i % this.palette.length]),
                                borderRadius: 4,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => this.moneyLabel(ctx.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: {
                                    beginAtZero: true,
                                    ticks: { callback: (value) => Stockflow.currency + Number(value).toLocaleString('en-PH', { maximumFractionDigits: 0 }) },
                                },
                            },
                        },
                    });
                },

                pctShare(parsed, data) {
                    const total = data.reduce((sum, row) => sum + Number(row.revenue), 0);
                    if (!total) return '0%';
                    return (Number(parsed) / total * 100).toFixed(0) + '%';
                },
            };
        }
    </script>
</x-layouts.app>