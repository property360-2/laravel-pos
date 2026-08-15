<x-layouts.app>
    <x-slot:title>Dashboard</x-slot>

    <div x-data="dashboardPage()" x-init="load()" class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="text-sm text-slate-500">Here's what's happening in your store today.</p>
            </div>
            <select x-model="period" @change="load()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                <option value="today">Today</option>
                <option value="week">This week</option>
                <option value="month">This month</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Products</p>
                    <span class="rounded-lg bg-indigo-50 p-2 text-xl leading-none"><span class="material-icons" aria-hidden="true">inventory_2</span></span>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900" x-text="metrics.total_products ?? '—'"></p>
                <p class="mt-1 text-xs text-slate-400">Active catalog items</p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Low Stock Alerts</p>
                    <span class="rounded-lg bg-red-50 p-2 text-xl leading-none"><span class="material-icons" aria-hidden="true">warning</span></span>
                </div>
                <p class="mt-2 text-3xl font-bold" :class="(metrics.low_stock_count ?? 0) > 0 ? 'text-red-600' : 'text-slate-900'" x-text="metrics.low_stock_count ?? '—'"></p>
                <p class="mt-1 text-xs text-slate-400">Items needing restock</p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Inventory Value</p>
                    <span class="rounded-lg bg-emerald-50 p-2 text-xl leading-none"><span class="material-icons" aria-hidden="true">payments</span></span>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900" x-text="metrics.inventory_value != null ? Stockflow.money(metrics.inventory_value) : '—'"></p>
                <p class="mt-1 text-xs text-slate-400">Stock × price across all products</p>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Sales (<span class="capitalize" x-text="period"></span>)</p>
                    <span class="rounded-lg bg-amber-50 p-2 text-xl leading-none"><span class="material-icons" aria-hidden="true">receipt_long</span></span>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900" x-text="metrics.sales_total != null ? Stockflow.money(metrics.sales_total) : '—'"></p>
                <p class="mt-1 text-xs text-slate-400"><span x-text="metrics.transactions_count ?? 0"></span> transactions</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h3 class="mb-4 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">emoji_events</span> Best Selling Products</h3>

                <div x-show="!loading && (!metrics.best_selling || metrics.best_selling.length === 0)" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                    No sales recorded for this period yet.
                </div>

                <ul class="divide-y divide-slate-100">
                    <template x-for="(item, index) in metrics.best_selling ?? []" :key="item.product_name">
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold" :class="index < 3 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'" x-text="index + 1"></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-800" x-text="item.product_name"></p>
                                <p class="text-xs text-slate-400"><span x-text="item.total_quantity"></span> sold</p>
                            </div>
                            <span class="text-sm font-semibold text-slate-700" x-text="Stockflow.money(item.total_revenue)"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">notification_important</span> Critical Stock Warnings</h3>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('inventory.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Manage inventory →</a>
                    @endif
                </div>

                <div x-show="!loading && (!metrics.critical_stock || metrics.critical_stock.length === 0)" class="rounded-lg border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                    All products are sufficiently stocked.
                </div>

                <ul class="divide-y divide-slate-100">
                    <template x-for="item in metrics.critical_stock ?? []" :key="item.id">
                        <li class="flex items-center gap-3 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-800" x-text="item.name"></p>
                                <p class="text-xs text-slate-400" x-text="item.sku"></p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold" :class="item.stock_quantity <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'">
                                <span x-text="item.stock_quantity"></span> left
                            </span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function dashboardPage() {
            return {
                metrics: null,
                period: 'today',
                loading: true,

                async load() {
                    this.loading = true;
                    try {
                        const { data } = await axios.get('/api/dashboard', { params: { period: this.period } });
                        this.metrics = data;
                    } catch {
                        this.$store.toast.error('Failed to load dashboard metrics.');
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-layouts.app>
