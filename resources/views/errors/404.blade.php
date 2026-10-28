<x-layout title="Not found">
    <div class="mx-auto max-w-xl px-4 py-24 text-center sm:px-6">
        <p class="text-sm font-semibold text-indigo-600">404</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight">This link doesn't exist</h1>
        <p class="mt-3 text-slate-600">It may have been mistyped, disabled or deleted by its owner.</p>
        <a href="{{ route('home') }}" class="btn-primary mt-8">Go to {{ config('app.name') }}</a>
    </div>
</x-layout>
