<fieldset class="space-y-3 rounded-xl border border-slate-700 bg-slate-900/40 p-4">
    <legend class="px-2 text-sm font-semibold text-slate-200">{{ $fighterLabel }}</legend>

    <div>
        <label for="{{ $fighterPrefix }}" class="mb-2 block text-sm font-medium text-slate-300">{{ $fighterLabel }} name</label>
        <input
            id="{{ $fighterPrefix }}"
            name="{{ $fighterPrefix }}"
            type="text"
            value="{{ $fighterName }}"
            @required($fighterNameRequired)
            class="w-full rounded-xl border border-slate-600 bg-slate-900 px-4 py-3 text-slate-100 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20"
        >
        @error($fighterPrefix)
            <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-x-5 gap-y-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="{{ $fighterPrefix }}_palong" value="1" @checked($fighterPalong) class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500">
            With Palong
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="{{ $fighterPrefix }}_lambe" value="1" @checked($fighterLambe) class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500">
            With Lambe
        </label>
    </div>

    <fieldset>
        <legend class="mb-2 text-sm font-medium text-slate-300">Leg Color</legend>
        <div class="flex flex-wrap gap-2">
            @foreach (['' => $fighterColorBlankLabel, 'White' => 'White', 'Yellow' => 'Yellow', 'Green' => 'Green', 'Dark' => 'Dark'] as $colorValue => $colorLabel)
                <label class="cursor-pointer">
                    <input
                        type="radio"
                        name="{{ $fighterPrefix }}_leg_color"
                        value="{{ $colorValue }}"
                        @checked($fighterLegColor === $colorValue)
                        class="peer sr-only"
                    >
                    <span class="inline-flex rounded-lg border border-slate-600 px-3 py-2 text-xs text-slate-300 transition peer-checked:border-amber-400 peer-checked:bg-amber-500/15 peer-checked:text-amber-200">
                        {{ $colorLabel }}
                    </span>
                </label>
            @endforeach
        </div>
        @error($fighterPrefix.'_leg_color')
            <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
        @enderror
    </fieldset>
</fieldset>
