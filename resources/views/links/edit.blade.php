<x-layout title="Edit link">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-10 sm:px-6">
        <div>
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">&larr; All links</a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight">{{ $link->displayTitle() }}</h1>
        </div>

        <section class="card">
            <p class="label">Short link</p>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ $link->shortUrl() }}" target="_blank" rel="noopener" class="break-all text-lg font-semibold text-indigo-600 hover:underline">{{ $link->shortUrl() }}</a>
                <div class="flex shrink-0 gap-2">
                    <x-copy-button :text="$link->shortUrl()" />
                    <a href="{{ $link->qrUrl() }}" class="btn-secondary px-3 py-1.5">QR code</a>
                </div>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-200 pt-4 text-sm">
                <div>
                    <dt class="text-slate-500">Clicks</dt>
                    <dd class="text-xl font-semibold tabular-nums">{{ number_format($link->clicks) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Created</dt>
                    <dd class="font-medium">{{ $link->created_at->toFormattedDateString() }}</dd>
                </div>
            </dl>
        </section>

        <form method="POST" action="{{ route('links.update', $link) }}" class="card space-y-4">
            @csrf
            @method('PUT')
            <h2 class="card-title">Settings</h2>

            <div>
                <label for="url" class="label">Destination URL</label>
                <input id="url" name="url" type="text" inputmode="url" required maxlength="2048" class="input" value="{{ old('url', $link->url) }}">
                <p class="mt-1 text-xs text-slate-500">Changing this updates the short link and any QR codes you've printed for it.</p>
                @error('url') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="title" class="label">Title <span class="optional">(optional)</span></label>
                <input id="title" name="title" type="text" maxlength="120" class="input" value="{{ old('title', $link->title) }}">
                @error('title') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-4 rounded border-slate-300 text-indigo-600" @checked(old('is_active', $link->is_active))>
                <span>
                    <span class="font-medium text-slate-700">Active</span>
                    <span class="block text-slate-500">When off, the short link shows a "not found" page.</span>
                </span>
            </label>

            <button type="submit" class="btn-primary">Save changes</button>
        </form>

        <form method="POST" action="{{ route('links.destroy', $link) }}" class="card flex flex-col gap-3 border-red-200 sm:flex-row sm:items-center sm:justify-between"
              onsubmit="return confirm('Delete this link? It will stop working, including any printed QR codes.')">
            @csrf
            @method('DELETE')
            <div>
                <h2 class="card-title">Delete link</h2>
                <p class="text-sm text-slate-500">The short link stops working immediately. This can't be undone.</p>
            </div>
            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
        </form>
    </div>
</x-layout>
