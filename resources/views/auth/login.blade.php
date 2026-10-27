<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign in · Fight Records</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="flex min-h-screen items-center justify-center bg-slate-900 px-4 text-slate-100">
        <main class="w-full max-w-md rounded-3xl border border-slate-700 bg-slate-950/80 p-8 shadow-2xl shadow-black/30">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-400">Fight Records</p>
            <h1 class="mt-3 text-3xl font-black text-white">Welcome back</h1>
            <p class="mt-2 text-sm text-slate-400">Sign in to search match records or manage the fight archive.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20">
                    @error('email')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20">
                    @error('password')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 font-bold text-slate-950 transition hover:bg-amber-400">
                    Sign in
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-400">
                New to Fight Records?
                <a href="{{ route('signup') }}" class="font-semibold text-amber-300 hover:text-amber-200">Create a player account</a>
            </p>
        </main>
    </body>
</html>
