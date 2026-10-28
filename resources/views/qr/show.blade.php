@php
    $typeLabels = ['url' => 'URL', 'text' => 'Text', 'wifi' => 'Wi-Fi', 'email' => 'Email', 'phone' => 'Phone', 'sms' => 'SMS'];
    $eccLabels = ['L' => 'Low (7%)', 'M' => 'Medium (15%)', 'Q' => 'Quartile (25%)', 'H' => 'High (30%)'];
    $sizes = [256, 512, 1024, 2048];

    $state = [
        'type' => old('type', $prefill['type']),
        'fg' => old('fg', $defaults->foreground),
        'bg' => old('bg', $defaults->background),
        'margin' => (int) old('margin', $defaults->margin),
        'wifiEncryption' => old('wifi_encryption', 'WPA'),
    ];
@endphp

<x-layout title="QR Code Generator" description="Free QR code generator for links, text, Wi-Fi, email, phone and SMS. Custom colours, PNG and SVG downloads.">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight">QR Code Generator</h1>
            <p class="mt-2 text-slate-600">Pick what the QR code should open, style it, then download it as PNG or SVG.</p>
        </div>

        <form
            x-data="qrGenerator(@js($state))"
            x-ref="form"
            data-preview-url="{{ route('qr.preview') }}"
            method="POST"
            action="{{ route('qr.download') }}"
            @input.debounce.300ms="refresh()"
            @change="refresh()"
            class="grid items-start gap-6 lg:grid-cols-[1fr_360px]"
        >
            @csrf

            <div class="space-y-6">
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Content --}}
                <section class="card">
                    <h2 class="card-title">1. Content</h2>

                    <fieldset class="mt-4">
                        <legend class="sr-only">QR code type</legend>
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                            @foreach ($types as $type)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $type }}" x-model="type" class="peer sr-only">
                                    <span class="block rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 hover:bg-slate-50">
                                        {{ $typeLabels[$type] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-5 space-y-4">
                        <div x-show="type === 'url'" x-cloak>
                            <label for="url" class="label">Website URL</label>
                            <input id="url" name="url" type="text" inputmode="url" autocomplete="url" class="input" placeholder="https://example.com" value="{{ old('url', $prefill['url']) }}">
                            @error('url') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="type === 'text'" x-cloak>
                            <label for="text" class="label">Text</label>
                            <textarea id="text" name="text" rows="4" class="input" maxlength="2000" placeholder="Any text you want to share">{{ old('text', $prefill['text']) }}</textarea>
                            @error('text') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="type === 'wifi'" x-cloak class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="wifi_ssid" class="label">Network name (SSID)</label>
                                <input id="wifi_ssid" name="wifi_ssid" type="text" class="input" maxlength="32" value="{{ old('wifi_ssid') }}">
                                @error('wifi_ssid') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="wifi_encryption" class="label">Security</label>
                                <select id="wifi_encryption" name="wifi_encryption" x-model="wifiEncryption" class="input">
                                    <option value="WPA">WPA / WPA2 / WPA3</option>
                                    <option value="WEP">WEP</option>
                                    <option value="nopass">No password</option>
                                </select>
                            </div>
                            <div x-show="wifiEncryption !== 'nopass'">
                                <label for="wifi_password" class="label">Password</label>
                                <input id="wifi_password" name="wifi_password" type="text" class="input" maxlength="63" autocomplete="off" value="{{ old('wifi_password') }}">
                                @error('wifi_password') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <label class="flex items-center gap-2 self-end pb-2 text-sm text-slate-700">
                                <input type="checkbox" name="wifi_hidden" value="1" class="size-4 rounded border-slate-300 text-indigo-600" @checked(old('wifi_hidden'))>
                                Hidden network
                            </label>
                        </div>

                        <div x-show="type === 'email'" x-cloak class="space-y-4">
                            <div>
                                <label for="email_to" class="label">Email address</label>
                                <input id="email_to" name="email_to" type="email" class="input" placeholder="hello@example.com" value="{{ old('email_to') }}">
                                @error('email_to') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email_subject" class="label">Subject <span class="optional">(optional)</span></label>
                                <input id="email_subject" name="email_subject" type="text" class="input" maxlength="200" value="{{ old('email_subject') }}">
                            </div>
                            <div>
                                <label for="email_body" class="label">Message <span class="optional">(optional)</span></label>
                                <textarea id="email_body" name="email_body" rows="3" class="input" maxlength="1000">{{ old('email_body') }}</textarea>
                            </div>
                        </div>

                        <div x-show="type === 'phone' || type === 'sms'" x-cloak class="space-y-4">
                            <div>
                                <label for="phone" class="label">Phone number</label>
                                <input id="phone" name="phone" type="tel" class="input" placeholder="+91 98765 43210" value="{{ old('phone') }}">
                                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="type === 'sms'">
                                <label for="sms_message" class="label">Message <span class="optional">(optional)</span></label>
                                <textarea id="sms_message" name="sms_message" rows="3" class="input" maxlength="500">{{ old('sms_message') }}</textarea>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Design --}}
                <section class="card">
                    <h2 class="card-title">2. Design</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="fg" class="label">QR colour</label>
                            <div class="flex gap-2">
                                <input type="color" x-model="fg" aria-label="Pick QR colour" class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                <input id="fg" name="fg" type="text" x-model="fg" class="input font-mono uppercase" maxlength="7">
                            </div>
                        </div>
                        <div>
                            <label for="bg" class="label">Background colour</label>
                            <div class="flex gap-2">
                                <input type="color" x-model="bg" aria-label="Pick background colour" class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                <input id="bg" name="bg" type="text" x-model="bg" class="input font-mono uppercase" maxlength="7">
                            </div>
                        </div>
                        <div>
                            <label for="margin" class="label">Margin <span class="optional" x-text="`(${margin})`"></span></label>
                            <input id="margin" name="margin" type="range" min="0" max="10" x-model.number="margin" class="mt-3 w-full accent-indigo-600">
                        </div>
                        <div>
                            <label for="ecc" class="label">Error correction</label>
                            <select id="ecc" name="ecc" class="input">
                                @foreach ($eccLevels as $level)
                                    <option value="{{ $level }}" @selected(old('ecc', $defaults->ecc) === $level)>{{ $eccLabels[$level] }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Higher levels still scan when partly damaged, but make the code denser.</p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Preview & download --}}
            <aside class="card lg:sticky lg:top-6">
                <h2 class="card-title">3. Download</h2>

                <div
                    class="relative mx-auto mt-4 grid aspect-square w-full max-w-72 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
                    :class="loading && 'opacity-60'"
                >
                    <img x-show="previewUrl" x-cloak :src="previewUrl" alt="QR code preview" class="size-full">
                    <p x-show="!previewUrl" class="px-8 text-center text-sm text-slate-400">Your QR code will appear here as you type.</p>
                </div>

                <p x-show="error" x-cloak x-text="error" class="mt-3 text-sm text-amber-700" role="status"></p>
                <p x-show="lowContrast" x-cloak class="mt-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    Low contrast: some phones may not scan this. Use a dark QR colour on a light background.
                </p>

                <div class="mt-5">
                    <label for="size" class="label">PNG size</label>
                    <select id="size" name="size" class="input">
                        @foreach ($sizes as $size)
                            <option value="{{ $size }}" @selected((int) old('size', $defaults->size) === $size)>{{ $size }} × {{ $size }} px</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="submit" name="format" value="png" :disabled="!previewUrl || error" class="btn-primary">Download PNG</button>
                    <button type="submit" name="format" value="svg" :disabled="!previewUrl || error" class="btn-secondary">Download SVG</button>
                </div>
                <p class="mt-3 text-xs text-slate-500">SVG is a vector file, so it stays sharp at any size. Use it for print.</p>
            </aside>
        </form>
    </div>
</x-layout>
