@php
    $shortBase = preg_replace('#^https?://#', '', url('/')).'/';
    $showOptions = $errors->hasAny(['title', 'alias']) || old('title') || old('alias');
@endphp

<x-layout title="Your links">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold tracking-tight">Short links</h1>

        {{-- Create --}}
        <form method="POST" action="{{ route('links.store') }}" class="card" x-data="{ options: @js((bool) $showOptions) }">
            @csrf
            <label for="url" class="label">Paste a long URL</label>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="url" name="url" type="text" inputmode="url" required maxlength="2048" class="input sm:text-base" placeholder="https://example.com/a/very/long/link" value="{{ old('url') }}" autofocus>
                <button type="submit" class="btn-primary shrink-0 sm:px-6">Shorten</button>
            </div>
            @error('url') <p class="field-error">{{ $message }}</p> @enderror

            <button type="button" @click="options = !options" class="mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                <span x-text="options ? 'Hide options' : 'Custom alias & title'">Custom alias &amp; title</span>
            </button>

            <div x-show="options" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="alias" class="label">Custom alias <span class="optional">(optional)</span></label>
                    <div class="flex rounded-lg shadow-xs">
                        <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ $shortBase }}</span>
                        <input id="alias" name="alias" type="text" minlength="3" maxlength="32" pattern="[A-Za-z0-9][A-Za-z0-9_\-]*" class="input rounded-l-none" placeholder="my-link" value="{{ old('alias') }}">
                    </div>
                    @error('alias') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="title" class="label">Title <span class="optional">(optional, only you see it)</span></label>
                    <input id="title" name="title" type="text" maxlength="120" class="input" placeholder="Spring sale landing page" value="{{ old('title') }}">
                    @error('title') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </form>

        {{-- Just created --}}
        @if ($created)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 sm:p-6" role="status">
                <p class="text-sm font-medium text-emerald-800">Your short link is ready</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $created->shortUrl() }}" target="_blank" rel="noopener" class="break-all text-xl font-semibold text-emerald-900 hover:underline">{{ $created->shortUrl() }}</a>
                    <div class="flex shrink-0 gap-2">
                        <x-copy-button :text="$created->shortUrl()" />
                        <a href="{{ $created->qrUrl() }}" class="btn-secondary px-3 py-1.5">QR code</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- List --}}
        <section class="card p-0 sm:p-0">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <h2 class="card-title">Your links <span class="font-normal text-slate-400">({{ $links->total() }})</span></h2>
                <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search links" aria-label="Search links" class="input sm:w-64">
                    @if ($search !== '')
                        <a href="{{ route('dashboard') }}" class="btn-secondary px-3">Clear</a>
                    @endif
                </form>
            </div>

            <ul class="divide-y divide-slate-200">
                @forelse ($links as $link)
                    <li class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:gap-6 sm:px-6">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-semibold">{{ $link->displayTitle() }}</p>
                                @unless ($link->is_active)
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Disabled</span>
                                @endunless
                            </div>
                            <a href="{{ $link->shortUrl() }}" target="_blank" rel="noopener" class="text-sm font-medium text-indigo-600 hover:underline">{{ $shortBase.$link->code }}</a>
                            <p class="truncate text-sm text-slate-500" title="{{ $link->url }}">{{ $link->url }}</p>
                        </div>

                        <div class="flex items-center gap-6 sm:shrink-0">
                            <div class="text-sm sm:text-right">
                                <p><span class="font-semibold tabular-nums">{{ number_format($link->clicks) }}</span> <span class="text-slate-500">{{ Str::plural('click', $link->clicks) }}</span></p>
                                <p class="text-xs text-slate-400" title="{{ $link->created_at->toDayDateTimeString() }}">{{ $link->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="ml-auto flex gap-2">
                                <x-copy-button :text="$link->shortUrl()" />
                                <a href="{{ $link->qrUrl() }}" class="btn-secondary px-3 py-1.5">QR</a>
                                <a href="{{ route('links.edit', $link) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-10 text-center text-sm text-slate-500">
                        @if ($search !== '')
                            No links match "{{ $search }}".
                        @else
                            You haven't shortened any links yet. Paste one above to get started.
                        @endif
                    </li>
                @endforelse
            </ul>

            @if ($links->hasPages())
                <div class="border-t border-slate-200 p-5 sm:px-6">{{ $links->links() }}</div>
            @endif
        </section>
    </div>
</x-layout>
