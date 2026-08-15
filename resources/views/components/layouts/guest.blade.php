<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="currency-symbol" content="{{ \App\Models\Setting::get('currency_symbol', '₱') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'StockFlow') - StockFlow POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    {{ $slot }}
    <x-toast />
</body>
</html>
