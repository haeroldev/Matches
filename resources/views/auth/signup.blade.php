<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create player account · Fight Records</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="flex min-h-screen items-center justify-center bg-slate-900 px-4 py-10 text-slate-100">
        <main class="w-full max-w-md rounded-3xl border border-slate-700 bg-slate-950/80 p-8 shadow-2xl shadow-black/30">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-400">Fight Records</p>
            <h1 class="mt-3 text-3xl font-black text-white">Join as a player</h1>
            <p class="mt-2 text-sm text-slate-400">Create an account to search and browse match records.</p>

            <form method="POST" action="{{ route('signup.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-200">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20">
                    @error('name')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20">
                    @error('email')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20">
                    @error('password')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-200">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20">
                </div>

                <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 font-bold text-slate-950 transition hover:bg-amber-400">
                    Create player account
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-amber-300 hover:text-amber-200">Sign in</a>
            </p>
        </main>
    </body>
</html>
