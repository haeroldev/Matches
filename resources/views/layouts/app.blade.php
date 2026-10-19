<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Fight Records')</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen bg-slate-900 text-slate-100">
        <nav class="border-b border-slate-700 bg-slate-950/80 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
                <a href="{{ route('dashboard') }}" class="text-xl font-black tracking-wide text-amber-400">Fight Admin</a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="rounded-full px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">Dashboard</a>
                    <a href="{{ route('matches.index') }}" class="rounded-full px-3 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">Matches</a>
                    <a href="{{ route('matches.create') }}" class="rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400">Add Match</a>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-10">
            @if (session('success'))
                <div id="successToast" class="pointer-events-none fixed bottom-5 right-5 z-50 w-[min(360px,calc(100vw-2rem))] rounded-xl border border-amber-400/60 bg-amber-500 px-4 py-3 shadow-2xl shadow-amber-500/20 backdrop-blur-sm" role="status" aria-live="polite">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-full bg-slate-950/10 text-slate-950">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M16.704 4.296a1 1 0 0 1 0 1.414l-7.071 7.071a1 1 0 0 1-1.414 0l-3.536-3.535a1 1 0 1 1 1.414-1.414l2.829 2.828 6.364-6.364a1 1 0 0 1 1.414 0Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-slate-950">Success</p>
                            <p class="mt-1 text-sm font-medium text-slate-950">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const toast = document.getElementById('successToast');
                        if (toast) {
                            setTimeout(function () {
                                toast.style.opacity = '0';
                                toast.style.transform = 'translateY(10px)';
                                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                setTimeout(function () {
                                    toast.remove();
                                }, 300);
                            }, 5000);
                        }
                    });
                </script>
            @endif

            @yield('content')
        </main>
    </body>
</html>
