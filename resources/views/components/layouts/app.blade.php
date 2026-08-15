@php
$user = auth()->user();
$currency = \App\Models\Setting::get('currency_symbol', '₱');

$navItems = [
    ['route' => 'dashboard', 'url' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'admin' => false],
    ['route' => 'pos', 'url' => '/pos', 'label' => 'POS', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', 'admin' => false],
    ['route' => 'products.index', 'url' => '/products', 'label' => 'Products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'admin' => true],
    ['route' => 'categories.index', 'url' => '/categories', 'label' => 'Categories', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'admin' => true],
    ['route' => 'inventory.index', 'url' => '/inventory', 'label' => 'Inventory', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', 'admin' => true],
    ['route' => 'transactions.index', 'url' => '/transactions', 'label' => 'Transactions', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'admin' => false],
    ['route' => 'reports.index', 'url' => '/reports', 'label' => 'Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'admin' => true],
    ['route' => 'settings.index', 'url' => '/settings', 'label' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'admin' => true],
];

$visibleNav = array_filter($navItems, fn ($item) => ! $item['admin'] || $user->isAdmin());
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="currency-symbol" content="{{ $currency }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - StockFlow POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt-print, #receipt-print * { visibility: visible; }
            #receipt-print { position: fixed; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">

        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center gap-2 border-b border-slate-800 px-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-lg"><span class="material-icons" aria-hidden="true">inventory_2</span></span>
                <div>
                    <p class="text-sm font-bold leading-tight">StockFlow</p>
                    <p class="text-[10px] uppercase tracking-widest text-slate-400">Point of Sale</p>
                </div>
            </div>

            <nav class="space-y-1 px-3 py-4">
                @foreach ($visibleNav as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                            {{ request()->is(trim($item['url'], '/')) || request()->is($item['url'].'/*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="absolute bottom-0 w-full border-t border-slate-800 p-3">
                <div class="mb-2 flex items-center gap-3 rounded-lg bg-slate-800/60 px-3 py-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold uppercase">
                        {{ substr($user->name, 0, 2) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                        <p class="text-[11px] capitalize text-slate-400">{{ $user->role }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-red-600/80 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        <div class="flex min-w-0 flex-1 flex-col">

            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
                <div class="flex items-center gap-3">
                    <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-slate-800">@yield('title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 sm:inline">
                        {{ $user->name }} · <span class="capitalize">{{ $user->role }}</span>
                    </span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold uppercase text-white">
                        {{ substr($user->name, 0, 2) }}
                    </span>
                </div>
            </header>

            <main class="flex-1 p-4 pb-24 sm:p-6 lg:pb-6">
                {{ $slot }}
            </main>

            <nav class="fixed bottom-0 left-0 z-20 flex w-full items-center justify-around border-t border-slate-200 bg-white py-2 shadow-lg md:hidden">
                @php
                    $mobileNav = array_filter($visibleNav, fn ($item) => in_array($item['route'], ['dashboard', 'pos', 'transactions']));
                @endphp
                @foreach ($mobileNav as $item)
                    <a href="{{ $item['url'] }}" class="flex flex-col items-center gap-0.5 px-4 py-1 text-[11px] font-medium {{ request()->is(trim($item['url'], '/')) ? 'text-indigo-600' : 'text-slate-500' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <x-toast />
</body>
</html>
