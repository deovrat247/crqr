@props(['title' => null, 'description' => 'Free QR code generator, URL shortener and link-in-bio pages.'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name').' · QR codes, short links & link-in-bio' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased flex flex-col">
    @php
        $navLink = fn (string $route) => 'rounded-lg px-3 py-2 hover:bg-slate-100'.(request()->routeIs($route) ? ' bg-slate-100 text-indigo-700' : '');
    @endphp

    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight">
                <span class="grid size-8 place-items-center rounded-lg bg-indigo-600 text-sm text-white">cr</span>
                <span class="hidden sm:inline">{{ config('app.name') }}</span>
            </a>
            <div class="flex items-center gap-1 text-sm font-medium">
                @auth
                    <a href="{{ route('dashboard') }}" class="{{ $navLink('dashboard') }}">Links</a>
                @endauth
                <a href="{{ route('qr') }}" class="{{ $navLink('qr') }}">QR Generator</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ $navLink('login') }}">Log in</a>
                    <a href="{{ route('register') }}" class="ml-1 rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700">Sign up</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @if (session('status'))
            <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-slate-500 sm:px-6">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
