<x-layout>
    <section class="mx-auto max-w-6xl px-4 py-16 text-center sm:px-6 sm:py-24">
        <h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">
            QR codes, short links and your link-in-bio page, in one place
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-lg text-slate-600">
            Shorten links you can edit later, see how many people click them, and turn any link into a QR code.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm hover:bg-indigo-700">
                Shorten a link
            </a>
            <a href="{{ route('qr') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                Create a QR code
            </a>
        </div>
    </section>

    <section class="mx-auto grid max-w-6xl gap-4 px-4 pb-20 sm:grid-cols-3 sm:px-6">
        @foreach ([
            ['Short links', 'Turn long URLs into '.parse_url(url('/'), PHP_URL_HOST).' links. Change where they point any time and count every click.', auth()->check() ? route('dashboard') : route('register')],
            ['QR Generator', 'Links, text, Wi-Fi, email, phone and SMS. Choose your colours and download PNG or SVG.', route('qr')],
            ['Link in bio', 'One page at '.parse_url(url('/'), PHP_URL_HOST).'/@you with all your important links.', null],
        ] as [$name, $text, $href])
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">{{ $name }}</h2>
                    @unless ($href)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Coming soon</span>
                    @endunless
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ $text }}</p>
                @if ($href)
                    <a href="{{ $href }}" class="mt-4 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-700">Open &rarr;</a>
                @endif
            </div>
        @endforeach
    </section>
</x-layout>
