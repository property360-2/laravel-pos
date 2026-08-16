<x-layouts.app>
    <x-slot:title>Settings</x-slot>

    <div x-data='settingsPage({ initial: @json($settings) })' class="mx-auto max-w-2xl space-y-6">

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="mb-1 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">storefront</span> Store Information</h3>
            <p class="mb-5 text-sm text-slate-500">These details appear on printed thermal receipts.</p>

            <form @submit.prevent="save()" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Store Name</label>
                    <input type="text" x-model="form.store_name" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                    <p class="mt-1 text-xs text-red-600" x-show="errors.store_name" x-text="errors.store_name?.[0]"></p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Store Address</label>
                    <input type="text" x-model="form.store_address" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Store Phone</label>
                    <input type="text" x-model="form.store_phone" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Currency Symbol</label>
                        <input type="text" maxlength="5" x-model="form.currency_symbol" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Receipt Footer Message</label>
                    <input type="text" x-model="form.receipt_footer" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                </div>

                <div class="flex justify-end pt-2">
                    <x-button type="submit" x-bind:disabled="saving" x-bind:class="{ 'is-loading': saving }"><span class="material-icons align-middle" aria-hidden="true">save</span> Save Settings</x-button>
                </div>
            </form>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="mb-1 font-semibold text-slate-800"><span class="material-icons align-middle" aria-hidden="true">person</span> Account</h3>
            <p class="text-sm text-slate-500">Signed in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role }})</p>
        </div>
    </div>

    <script>
        function settingsPage({ initial }) {
            return {
                form: { ...initial },
                errors: {},
                saving: false,

                async save() {
                    this.saving = true;
                    this.errors = {};
                    try {
                        const { data } = await axios.put('/api/settings', { settings: this.form });
                        this.form = { ...data };
                        this.$store.toast.success('Settings saved successfully.');
                    } catch (error) {
                        this.errors = error.response?.data?.errors?.settings ?? {};
                        this.$store.toast.error('Failed to save settings.');
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
</x-layouts.app>
