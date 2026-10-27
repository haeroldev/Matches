@extends('layouts.app')

@section('title', 'Fighters')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-amber-400">Maintenance</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Fighters</h1>
            <p class="mt-2 text-sm text-slate-400">Unique fighter names recorded in match history.</p>
        </div>

        <span class="rounded-full border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-slate-300">
            {{ $fighters->total() }} {{ \Illuminate\Support\Str::plural('fighter', $fighters->total()) }}
        </span>
    </div>

    @if ($fighters->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-950/40 p-10 text-center text-slate-400">
            No fighters found. Add a match to start the fighter list.
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-950/60 shadow-xl shadow-slate-950/20">
            <ul class="divide-y divide-slate-800">
                @foreach ($fighters as $fighter)
                    <li class="flex items-center gap-4 px-5 py-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-sm font-semibold text-amber-300">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($fighter->name, 0, 1)) }}
                        </span>
                        <span class="font-medium text-slate-100">{{ \Illuminate\Support\Str::ucfirst($fighter->name) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($fighters->hasPages())
            <div class="mt-6 flex justify-center">
                <div class="rounded-xl border border-slate-700 bg-slate-950/60 px-3 py-2">
                    {{ $fighters->links() }}
                </div>
            </div>
        @endif
    @endif
@endsection
