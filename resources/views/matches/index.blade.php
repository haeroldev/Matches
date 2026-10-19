@extends('layouts.app')

@section('title', 'All Matches')

@section('content')
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-amber-400">Administration</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Fight Records</h1>
        </div>

        <a href="{{ route('matches.create') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 font-semibold text-slate-950 transition hover:bg-amber-400">
            + Add Match
        </a>
    </div>

    <div class="rounded-2xl border border-slate-700 bg-slate-950/60 p-4 shadow-xl shadow-slate-950/30">
        <form method="GET" action="{{ route('matches.index') }}" class="space-y-4">
            <input type="hidden" name="filter_fighters" value="1">
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    value="{{ old('search', $search) }}"
                    placeholder="Search by fighter, winner or description"
                    class="w-full rounded-xl border border-slate-600 bg-slate-900 px-4 py-3 pr-11 text-slate-100 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20"
                >
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-500">⌕</span>
            </div>

            <p class="text-xs text-slate-400">Unchecked Palong/Lambe boxes filter for No; choose a leg color to narrow results by color.</p>
            <div class="grid gap-4 lg:grid-cols-2">
                @include('matches.partials.fighter-fields', [
                    'fighterPrefix' => 'fighter_1',
                    'fighterLabel' => 'Fighter A',
                    'fighterName' => $fighter1,
                    'fighterNameRequired' => false,
                    'fighterPalong' => $fighter1Palong,
                    'fighterLambe' => $fighter1Lambe,
                    'fighterLegColor' => $fighter1LegColor,
                    'fighterColorBlankLabel' => 'Any color',
                ])

                @include('matches.partials.fighter-fields', [
                    'fighterPrefix' => 'fighter_2',
                    'fighterLabel' => 'Fighter B',
                    'fighterName' => $fighter2,
                    'fighterNameRequired' => false,
                    'fighterPalong' => $fighter2Palong,
                    'fighterLambe' => $fighter2Lambe,
                    'fighterLegColor' => $fighter2LegColor,
                    'fighterColorBlankLabel' => 'Any color',
                ])
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-amber-500 px-5 py-3 font-medium text-slate-950 transition hover:bg-amber-400">
                    Search
                </button>
                @if ($search || $fighter1 || $fighter2 || $fighterFiltersActive)
                    <a href="{{ route('matches.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-600 px-5 py-3 font-medium text-slate-200 transition hover:bg-slate-800">
                        Clear
                    </a>
                @endif
            </div>
        </form>
        <div class="mt-4 text-sm text-slate-400">
            Showing {{ $matches->firstItem() ?? 0 }}-{{ $matches->lastItem() ?? 0 }} of {{ $matches->total() }}
        </div>

        @if ($filteredWinStats !== null)
            <div class="mt-5 rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4">
                <div class="mb-3">
                    <h2 class="font-semibold text-white">Win rate for filtered fights</h2>
                    <p class="text-xs text-slate-400">Percentages are calculated from all fights matching these filters.</p>
                </div>

                @if ($filteredWinStats['total'] === 0)
                    <p class="text-sm text-slate-300">No filtered fights to calculate a win rate.</p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg bg-slate-900/80 p-3">
                            <p class="truncate text-sm text-red-300">{{ $fighter1 !== '' ? $fighter1 : 'Fighter A' }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ number_format($filteredWinStats['fighter_1_percentage'], 1) }}%</p>
                            <p class="text-xs text-slate-400">{{ $filteredWinStats['fighter_1_wins'] }} wins</p>
                        </div>
                        <div class="rounded-lg bg-slate-900/80 p-3">
                            <p class="truncate text-sm text-blue-300">{{ $fighter2 !== '' ? $fighter2 : 'Fighter B' }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ number_format($filteredWinStats['fighter_2_percentage'], 1) }}%</p>
                            <p class="text-xs text-slate-400">{{ $filteredWinStats['fighter_2_wins'] }} wins</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">Based on {{ $filteredWinStats['total'] }} filtered {{ \Illuminate\Support\Str::plural('fight', $filteredWinStats['total']) }}.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($matches as $match)
            <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-950/60 shadow-xl shadow-slate-950/20 transition hover:border-amber-500/40 hover:shadow-amber-500/10">
                @if ($match->image_path)
                    <button type="button" class="image-trigger block w-full bg-slate-900 p-0" data-image="{{ asset($match->image_path) }}" data-title="{{ $match->fighter_1 }} vs {{ $match->fighter_2 }}">
                        <img src="{{ asset($match->image_path) }}" alt="{{ $match->fighter_1 }} vs {{ $match->fighter_2 }}" class="h-56 w-full object-cover transition hover:scale-[1.02]">
                    </button>
                @else
                    <div class="flex h-56 w-full items-center justify-center bg-slate-800 text-xs uppercase tracking-[0.25em] text-slate-400">No Image</div>
                @endif

                <div class="space-y-3 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Fighters</p>
                            <p class="mt-1 text-base font-semibold text-slate-100">
                                <span class="text-red-500">{{ $match->fighter_1 }}</span>
                                <span class="text-slate-400">vs</span>
                                <span class="text-blue-500">{{ $match->fighter_2 }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <a href="{{ route('matches.edit', $match) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-500/40 bg-blue-500/10 text-blue-300 transition hover:bg-blue-500/20" title="Edit match" aria-label="Edit match">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                    <path d="M5.433 13.917 9.4 12.5l4.966-4.966a2.12 2.12 0 1 0-3 3L6.4 15.5l-.967-1.583ZM4.5 16.5l1.3-2.13 1.83 1.83L4.5 16.5Z"/>
                                </svg>
                            </a>
                            <form action="{{ route('matches.destroy', $match) }}" method="POST" onsubmit="return confirm('Delete this match?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-500/40 bg-rose-500/10 text-rose-300 transition hover:bg-rose-500/20" title="Delete match" aria-label="Delete match">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                        <path fill-rule="evenodd" d="M8.75 1.5A1.25 1.25 0 0 0 7.5 2.75v.75H4.5A1.25 1.25 0 0 0 3.25 4.75v.5c0 .69.56 1.25 1.25 1.25h.78l.69 9.75A1.75 1.75 0 0 0 7.7 18.25h4.6a1.75 1.75 0 0 0 1.73-1.5l.69-9.75h.78c.69 0 1.25-.56 1.25-1.25v-.5a1.25 1.25 0 0 0-1.25-1.25h-3V2.75A1.25 1.25 0 0 0 11.25 1.5h-2.5Zm1.75 2.5v-.75h-1.5v.75h1.5Zm-3.29 3.01h.5l.44 6.5h-.5l-.44-6.5Zm4.54 0h.5l-.44 6.5h-.5l.44-6.5Zm-2.67 0h1.5v6.5h-1.5v-6.5Z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Description</p>
                        <p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-300">
                            {{ $match->description ?? 'No description available.' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Winner</p>
                        <p class="mt-1 text-lg font-bold text-amber-400">{{ $match->winner }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-700 bg-slate-950/40 p-10 text-center text-slate-400">
                No matches found. Try another search or add a match.
            </div>
        @endforelse
    </div>

    @if ($matches->hasPages())
        <div class="mt-6 flex justify-center">
            <div class="rounded-xl border border-slate-700 bg-slate-950/60 px-3 py-2">
                {{ $matches->links() }}
            </div>
        </div>
    @endif

    <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 p-4">
        <div class="relative max-w-4xl w-full rounded-2xl border border-slate-700 bg-slate-900 p-3 shadow-2xl shadow-slate-950/50">
            <button type="button" id="closeModal" class="absolute right-4 top-4 z-10 rounded-full bg-slate-800 px-3 py-1 text-lg text-white hover:bg-slate-700">×</button>
            <img id="modalImage" src="" alt="Fight preview" class="max-h-[80vh] w-full rounded-xl object-contain">
            <div class="mt-3 px-2 pb-1 text-center">
                <p id="modalTitle" class="text-lg font-semibold text-white"></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const closeModal = document.getElementById('closeModal');

            document.querySelectorAll('.image-trigger').forEach(function (button) {
                button.addEventListener('click', function () {
                    const imageUrl = button.dataset.image;
                    const title = button.dataset.title;
                    modalImage.src = imageUrl;
                    modalTitle.textContent = title;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            closeModal.addEventListener('click', function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modalImage.src = '';
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modalImage.src = '';
                }
            });
        });
    </script>
@endsection
