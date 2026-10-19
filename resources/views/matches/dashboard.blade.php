@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-amber-400">Overview</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Admin Dashboard</h1>
        </div>

        <a href="{{ route('matches.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 font-semibold text-slate-950 transition hover:bg-amber-400">
            + Add Match
        </a>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-amber-500/20 to-slate-900 p-5 shadow-xl shadow-slate-950/20">
            <p class="text-sm uppercase tracking-[0.2em] text-amber-300">Total Fights</p>
            <h2 class="mt-4 text-4xl font-black text-white">{{ $totalMatches }}</h2>
        </div>

        <div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-sky-500/20 to-slate-900 p-5 shadow-xl shadow-slate-950/20">
            <p class="text-sm uppercase tracking-[0.2em] text-sky-300">Recent</p>
            <h2 class="mt-4 text-4xl font-black text-white">{{ $recentMatches->count() }}</h2>
        </div>

        <div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-emerald-500/20 to-slate-900 p-5 shadow-xl shadow-slate-950/20">
            <p class="text-sm uppercase tracking-[0.2em] text-emerald-300">Top Winner</p>
            <h2 class="mt-4 text-xl font-black text-white">
                {{ $winnerStats->first()?->winner ?? 'N/A' }}
            </h2>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-2xl border border-slate-700 bg-slate-950/60 p-5 shadow-xl shadow-slate-950/20">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Recent Matches</h3>
                <a href="{{ route('matches.index') }}" class="text-sm font-medium text-amber-300 hover:text-amber-200">View all</a>
            </div>

            <div class="space-y-4">
                @forelse ($recentMatches as $match)
                    <div class="flex items-center gap-4 rounded-xl border border-slate-800 bg-slate-900/60 p-3">
                        @if ($match->image_path)
                            <img src="{{ asset($match->image_path) }}" alt="{{ $match->fighter_1 }} vs {{ $match->fighter_2 }}" class="h-16 w-24 rounded-lg object-cover">
                        @else
                            <div class="flex h-16 w-24 items-center justify-center rounded-lg bg-slate-800 text-[10px] uppercase text-slate-400">No Image</div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-lg font-semibold text-white">{{ $match->fighter_1 }} vs {{ $match->fighter_2 }}</p>
                            <p class="mt-1 text-sm text-slate-400">Winner: <span class="font-medium text-amber-300">{{ $match->winner }}</span></p>
                        </div>

                        <div class="text-right text-sm text-slate-400">
                            <p>{{ $match->created_at->format('M d') }}</p>
                            <div class="mt-2 flex gap-2 justify-end">
                                <a href="{{ route('matches.edit', $match) }}" class="text-blue-300 hover:text-blue-200">Edit</a>
                                <form action="{{ route('matches.destroy', $match) }}" method="POST" onsubmit="return confirm('Delete this match?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-300 hover:text-rose-200">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-700 p-6 text-center text-slate-400">
                        No fights yet. Add your first match to get started.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-700 bg-slate-950/60 p-5 shadow-xl shadow-slate-950/20">
            <h3 class="text-xl font-bold text-white">Winner Summary</h3>

            <div class="mt-5 space-y-4">
                @forelse ($winnerStats as $winner)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm text-slate-300">
                            <span>{{ $winner->winner }}</span>
                            <span>{{ $winner->total }} wins</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-amber-500" style="width: {{ min(($winner->total / max($totalMatches, 1)) * 100, 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No winners available yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
