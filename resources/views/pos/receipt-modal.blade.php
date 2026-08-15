<x-modal show="receiptOpen" maxWidth="max-w-sm">
    <div class="p-5 sm:p-6">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900"><span class="material-icons align-middle" aria-hidden="true">receipt_long</span> Receipt</h3>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">PAID</span>
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
                    <div class="flex justify-between font-semibold">
                        <span>Item</span><span>Qty</span><span>Price</span><span>Subtotal</span>
                    </div>
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
            <x-button variant="secondary" class="flex-1" @click="closeReceipt()">Close</x-button>
            <x-button class="flex-1" @click="window.print()"><span class="material-icons align-middle" aria-hidden="true">print</span> Print Receipt</x-button>
        </div>
    </div>
</x-modal>
