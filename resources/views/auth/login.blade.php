<x-layouts.guest>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-3xl shadow-lg shadow-indigo-600/30"><span class="material-icons" aria-hidden="true">inventory_2</span></span>
                <h1 class="text-2xl font-bold text-slate-900">StockFlow</h1>
                <p class="mt-1 text-sm text-slate-500">Sign in to your point of sale account</p>
            </div>

            <form class="rounded-2xl bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-8"
                 x-data="{ email: '', password: '', remember: false, loading: false, error: '', errors: {} }"
                 @submit.prevent="
                    loading = true; error = ''; errors = {};
                    axios.post('/api/login', { email, password, remember })
                        .then((response) => { window.location = response.data.redirect; })
                        .catch((e) => {
                            if (e.response?.status === 422 && e.response?.data?.errors) { errors = e.response.data.errors; }
                            else { error = e.response?.data?.message || 'Invalid credentials. Please try again.'; }
                        })
                        .finally(() => { loading = false; });
                 "
            >
                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-inset ring-red-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div x-show="error" x-cloak x-text="error" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-inset ring-red-200"></div>

                <div class="space-y-4">
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email address</label>
                        <input id="email" type="email" x-model="email" required autocomplete="email" placeholder="admin@stockflow.test"
                               class="block w-full rounded-lg border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600" />
                        <p class="mt-1.5 text-xs text-red-600" x-show="errors.email" x-text="errors.email?.[0]"></p>
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <input id="password" type="password" x-model="password" required autocomplete="current-password" placeholder="••••••••"
                               class="block w-full rounded-lg border-0 px-3 py-2 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600" />
                        <p class="mt-1.5 text-xs text-red-600" x-show="errors.password" x-text="errors.password?.[0]"></p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" x-model="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        Remember me
                    </label>
                </div>

                <div class="mt-6">
                    <x-button type="submit" size="lg" class="w-full" x-bind:disabled="loading" x-bind:class="{ 'is-loading': loading }">
                        <span x-text="loading ? 'Signing in...' : 'Sign in'">Sign in</span>
                    </x-button>
                </div>

                <div class="mt-6 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">
                    <p class="mb-1 font-semibold text-slate-600">Demo accounts:</p>
                    <p>Admin: admin@stockflow.test / password</p>
                    <p>Cashier: cashier@stockflow.test / password</p>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
