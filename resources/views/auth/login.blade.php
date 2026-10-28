<x-layout title="Log in">
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight">Log in</h1>
        <p class="mt-2 text-sm text-slate-600">Welcome back.</p>

        <form method="POST" action="{{ route('login') }}" class="card mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required class="input" value="{{ old('email') }}" autofocus>
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="input">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-indigo-600" @checked(old('remember'))>
                Keep me logged in
            </label>

            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            New here? <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Create an account</a>
        </p>
    </div>
</x-layout>
