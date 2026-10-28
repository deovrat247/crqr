<x-layout title="Sign up">
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight">Create your account</h1>
        <p class="mt-2 text-sm text-slate-600">Free. Shorten links, track clicks and make QR codes.</p>

        <form method="POST" action="{{ route('register') }}" class="card mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="label">Name</label>
                <input id="name" name="name" type="text" autocomplete="name" required maxlength="80" class="input" value="{{ old('name') }}" autofocus>
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="username" class="label">Username</label>
                <div class="flex rounded-lg shadow-xs">
                    <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ parse_url(url('/'), PHP_URL_HOST) }}/@</span>
                    <input id="username" name="username" type="text" autocomplete="username" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_]+" class="input rounded-l-none" value="{{ old('username') }}">
                </div>
                <p class="mt-1 text-xs text-slate-500">Your future link-in-bio address. Letters, numbers and underscores.</p>
                @error('username') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required class="input" value="{{ old('email') }}">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" class="input">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="input">
            </div>

            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Already have an account? <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Log in</a>
        </p>
    </div>
</x-layout>
