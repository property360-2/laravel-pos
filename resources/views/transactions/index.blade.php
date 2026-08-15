<x-layouts.app>
    <x-slot:title>Transactions</x-slot>

    <div x-data="transactionsPage()" x-init="init()" class="space-y-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 sm:max-w-xs">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search by transaction #..."
                    class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-4 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600"
                />
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
            </div>
            <input type="date" x-model="startDate" @change="load()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
            <input type="date" x-model="endDate" @change="load()" class="rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
        </div>

        <x-table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Transaction #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Cashier</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Payment</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="transaction in transactions" :key="transaction.id">
                    <tr class="cursor-pointer hover:bg-slate-50" @click="openReceipt(transaction.id)">
                        <td class="px-4 py-3 font-medium text-indigo-600" x-text="transaction.transaction_number"></td>
                        <td class="px-4 py-3 text-slate-600" x-text="transaction.user?.name"></td>
                        <td class="px-4 py-3 text-right text-slate-600" x-text="transaction.items_count"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-700" x-text="transaction.payment_method.toUpperCase()"></span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800" x-text="Stockflow.money(transaction.total)"></td>
                        <td class="px-4 py-3 text-slate-500" x-text="Stockflow.formatDate(transaction.created_at)"></td>
                    </tr>
                </template>
            </tbody>
        </x-table>

        <div x-show="!loading && transactions.length === 0" class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
            <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">receipt_long</span></span>
            <p class="text-sm">No transactions found for this filter.</p>
        </div>

        <div class="flex items-center justify-between" x-show="lastPage > 1">
            <p class="text-xs text-slate-500">Page <span x-text="page"></span> of <span x-text="lastPage"></span></p>
            <div class="flex gap-2">
                <x-button size="sm" variant="secondary" x-bind:disabled="page <= 1" @click="page--; load()">← Previous</x-button>
                <x-button size="sm" variant="secondary" x-bind:disabled="page >= lastPage" @click="page++; load()">Next →</x-button>
            </div>
        </div>

        <x-modal show="receiptOpen" maxWidth="max-w-sm">
            <div class="p-5 sm:p-6">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900"><span class="material-icons align-middle" aria-hidden="true">receipt_long</span> Receipt</h3>
                </div>

                <template x-if="receipt">
                    <div id="receipt-print" class="mx-auto max-w-[300px] rounded-lg bg-white p-4 font-mono text-[11px] leading-relaxed text-slate-900 ring-1 ring-slate-200">
                        <div class="text-center">
                            <p class="text-sm font-bold uppercase" x-text="receipt.store.name"></p>
                            <p x-text="receipt.store.address"></p>
                            <p x-text="'TEL: ' + receipt.store.phone"></p>
                            <p class="my-2">==================================================</p>
                            <p>Receipt #: <span x-text="receipt.transaction.transaction_number"></span></p>
                            <p>Date: <span x-text="Stockflow.formatDate(receipt.transaction.created_at)"></span></p>
                            <p>Cashier: <span x-text="receipt.transaction.user?.name"></span></p>
                            <p class="my-2">--------------------------------------------------</p>
                        </div>

                        <template x-for="item in receipt.transaction.items" :key="item.id">
                            <div class="mb-1">
                                <p class="truncate font-semibold" x-text="item.product_name"></p>
                                <div class="flex justify-between">
                                    <span></span>
                                    <span x-text="item.quantity"></span>
                                    <span x-text="Stockflow.money(item.unit_price)"></span>
                                    <span x-text="Stockflow.money(item.subtotal)"></span>
                                </div>
                            </div>
                        </template>

                        <p class="my-2">--------------------------------------------------</p>
                        <div class="flex justify-between"><span>Subtotal:</span><span x-text="Stockflow.money(receipt.transaction.subtotal)"></span></div>
                        <div class="flex justify-between"><span>Discount:</span><span x-text="Stockflow.money(receipt.transaction.discount)"></span></div>
                        <p class="my-2">--------------------------------------------------</p>
                        <div class="flex justify-between text-sm font-bold"><span>TOTAL:</span><span x-text="Stockflow.money(receipt.transaction.total)"></span></div>
                        <div class="flex justify-between"><span class="capitalize" x-text="receipt.transaction.payment_method + ':'"></span><span x-text="Stockflow.money(receipt.transaction.amount_paid)"></span></div>
                        <div class="flex justify-between font-bold"><span>CHANGE:</span><span x-text="Stockflow.money(receipt.transaction.change_amount)"></span></div>
                        <p class="my-2">==================================================</p>
                        <p class="text-center uppercase" x-text="receipt.store.receipt_footer"></p>
                    </div>
                </template>

                <div class="mt-5 flex gap-3">
                    <x-button variant="secondary" class="flex-1" @click="receiptOpen = false">Close</x-button>
                    <x-button class="flex-1" @click="window.print()"><span class="material-icons align-middle" aria-hidden="true">print</span> Re-print Receipt</x-button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        function transactionsPage() {
            return {
                transactions: [],
                search: '',
                startDate: '',
                endDate: '',
                page: 1,
                lastPage: 1,
                loading: true,
                receiptOpen: false,
                receipt: null,

                async init() {
                    await this.load();
                    this.$watch('search', () => { this.page = 1; this.load(); });
                },

                async load() {
                    this.loading = true;
                    try {
                        const params = { page: this.page, per_page: 15 };
                        if (this.search) params.search = this.search;
                        if (this.startDate) params.start_date = this.startDate;
                        if (this.endDate) params.end_date = this.endDate;
                        const { data } = await axios.get('/api/transactions', { params });
                        this.transactions = data.data;
                        this.lastPage = data.last_page;
                    } catch {
                        this.$store.toast.error('Failed to load transactions.');
                    } finally {
                        this.loading = false;
                    }
                },

                async openReceipt(id) {
                    try {
                        const { data } = await axios.get(`/api/transactions/${id}`);
                        this.receipt = data;
                        this.receiptOpen = true;
                    } catch {
                        this.$store.toast.error('Failed to load receipt.');
                    }
                },
            };
        }
    </script>
</x-layouts.app>
