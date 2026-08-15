<x-layouts.app>
    <x-slot:title>Point of Sale</x-slot>

    <div x-data="posCart()" x-cloak class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:h-[calc(100vh-7rem)] lg:flex-row">

        <div class="flex min-h-0 flex-1 flex-col rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model.debounce.300ms="search"
                        placeholder="Search products..."
                        class="w-full rounded-lg border-0 bg-slate-50 py-2 pl-9 pr-4 text-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600"
                    />
                </div>
                <select x-model="categoryId" @change="loadProducts()" class="rounded-lg border-0 bg-slate-50 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                    <option value="">All Categories</option>
                    <template x-for="category in categories" :key="category.id">
                        <option :value="category.id" x-text="category.name"></option>
                    </template>
                </select>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-4">
                <div x-show="loadingProducts" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                    <template x-for="i in 8" :key="i">
                        <div class="h-28 animate-pulse rounded-xl bg-slate-100"></div>
                    </template>
                </div>

                <div x-show="!loadingProducts && filteredProducts.length === 0" class="flex h-full flex-col items-center justify-center gap-2 text-center text-slate-400">
                    <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">search</span></span>
                    <p class="text-sm">No products found.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button
                            @click="addToCart(product)"
                            class="group flex flex-col rounded-xl border border-slate-200 bg-white p-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md"
                            :disabled="product.stock_quantity <= 0"
                            :class="product.stock_quantity <= 0 ? 'opacity-50' : ''"
                        >
                            <p class="text-sm font-semibold leading-tight text-slate-800 group-hover:text-indigo-700" x-text="product.name"></p>
                            <p class="mt-0.5 text-xs text-slate-400" x-text="product.sku"></p>
                            <div class="mt-auto flex items-center justify-between pt-2">
                                <span class="text-sm font-bold text-indigo-600" x-text="Stockflow.money(product.price)"></span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                      :class="product.stock_status === 'in_stock' ? 'bg-emerald-50 text-emerald-600' : (product.stock_status === 'low_stock' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')"
                                      x-text="product.stock_quantity <= 0 ? 'Out' : product.stock_quantity + ' left'">
                                </span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex min-h-0 w-full flex-col rounded-xl bg-white shadow-sm ring-1 ring-slate-200 lg:w-96 lg:flex-none">
            <div class="flex items-center justify-between border-b border-slate-200 p-4">
                <h3 class="font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">shopping_cart</span> Cart <span class="text-sm font-normal text-slate-400">(<span x-text="cartCount"></span> items)</span></h3>
                <button x-show="cart.length > 0" @click="clearCart()" class="text-xs font-medium text-red-500 hover:text-red-600">Clear all</button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-4">
                <div x-show="cart.length === 0" class="flex h-full flex-col items-center justify-center gap-2 text-center text-slate-400">
                    <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">shopping_cart</span></span>
                    <p class="text-sm">Cart is empty.<br>Click a product to add it.</p>
                </div>

                <ul class="space-y-3">
                    <template x-for="line in cart" :key="line.product_id">
                        <li class="rounded-xl border border-slate-200 p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800" x-text="line.name"></p>
                                    <p class="text-xs text-slate-400" x-text="Stockflow.money(line.unit_price) + ' each'"></p>
                                </div>
                                <span class="text-sm font-bold text-slate-800" x-text="Stockflow.money(line.unit_price * line.quantity)"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button @click="decrement(line)" class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 font-bold text-slate-600 hover:bg-slate-200">−</button>
                                    <span class="w-8 text-center text-sm font-semibold" x-text="line.quantity"></span>
                                    <button @click="increment(line)" class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 font-bold text-slate-600 hover:bg-slate-200">+</button>
                                </div>
                                <button @click="removeFromCart(line)" class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-500" title="Remove"><span class="material-icons" aria-hidden="true">delete</span></button>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="border-t border-slate-200 p-4">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-slate-500">Subtotal</span>
                    <span class="font-medium text-slate-800" x-text="Stockflow.money(subtotal)"></span>
                </div>
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-slate-500">
                        Discount
                        <label class="flex items-center gap-1 text-xs">
                            <input type="checkbox" x-model="discountIsPercent" class="h-3 w-3 rounded border-slate-300 text-indigo-600" />
                            %
                        </label>
                    </span>
                    <div class="flex items-center gap-1">
                        <span x-show="discountIsPercent" class="text-slate-400">%</span>
                        <input type="number" min="0" step="0.01" x-model="discountInput" class="w-24 rounded-md border-0 bg-slate-50 px-2 py-1 text-right text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                    </div>
                </div>
                <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-2">
                    <span class="font-semibold text-slate-800">Total</span>
                    <span class="text-xl font-bold text-indigo-600" x-text="Stockflow.money(total)"></span>
                </div>
                <x-button size="lg" class="mt-4 w-full" x-bind:disabled="cart.length === 0" @click="openCheckout()">
                    <span class="material-icons align-middle" aria-hidden="true">credit_card</span> Pay &amp; Checkout (<span x-text="Stockflow.money(total)"></span>)
                </x-button>
            </div>
        </div>

        <x-modal show="checkoutOpen" maxWidth="max-w-md">
            <div class="p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900"><span class="material-icons align-middle" aria-hidden="true">credit_card</span> Checkout &amp; Payment</h3>

                <div class="mb-4 flex items-center justify-between rounded-xl bg-indigo-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-600">Total Amount Due</span>
                    <span class="text-xl font-bold text-indigo-700" x-text="Stockflow.money(total)"></span>
                </div>

                <p class="mb-1.5 text-sm font-medium text-slate-700">Payment Method</p>
                <div class="mb-4 grid grid-cols-3 gap-2">
                    <template x-for="method in [{value:'cash',label:'Cash'},{value:'gcash',label:'GCash'},{value:'card',label:'Card'}]" :key="method.value">
                        <button
                            @click="form.payment_method = method.value"
                            class="rounded-lg border px-3 py-2 text-sm font-medium transition"
                            :class="form.payment_method === method.value ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300'"
                            x-text="method.label"
                        ></button>
                    </template>
                </div>

                <div class="mb-3">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Amount Received</label>
                    <input type="number" min="0" step="0.01" x-model="form.amount_paid" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <template x-for="amount in [50, 100, 500, 1000]" :key="amount">
                        <button @click="quickCash(amount)" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200" x-text="'₱' + amount"></button>
                    </template>
                    <button @click="quickCash(total)" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-200">Exact</button>
                </div>

                <div class="mb-5 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-600">Change Given</span>
                    <span class="text-lg font-bold text-emerald-600" x-text="Stockflow.money(change)"></span>
                </div>

                <div class="flex gap-3">
                    <x-button variant="secondary" class="flex-1" @click="checkoutOpen = false">Cancel</x-button>
                    <x-button variant="success" class="flex-1" x-bind:disabled="!canCheckout() || processing" x-bind:class="{ 'is-loading': processing }" @click="checkout()">
                        Complete Sale
                    </x-button>
                </div>
            </div>
        </x-modal>

        @include('pos.receipt-modal')
    </div>
</x-layouts.app>
