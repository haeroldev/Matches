<?php

namespace App\Http\Controllers;

use App\Models\FightMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MatchController extends Controller
{
    public function dashboard()
    {
        if (! Schema::hasTable('matches')) {
            return view('matches.dashboard', [
                'totalMatches' => 0,
                'recentMatches' => collect(),
                'winnerStats' => collect(),
            ]);
        }

        $totalMatches = FightMatch::count();
        $recentMatches = FightMatch::latest()->take(5)->get();
        $winnerStats = FightMatch::selectRaw('winner, count(*) as total')
            ->groupBy('winner')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('matches.dashboard', compact('totalMatches', 'recentMatches', 'winnerStats'));
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $fighter1 = trim((string) $request->query('fighter_1', ''));
        $fighter2 = trim((string) $request->query('fighter_2', ''));
        $fighterFiltersActive = $request->boolean('filter_fighters');
        $fighter1Palong = $request->boolean('fighter_1_palong');
        $fighter1Lambe = $request->boolean('fighter_1_lambe');
        $fighter1LegColor = (string) $request->query('fighter_1_leg_color', '');
        $fighter2Palong = $request->boolean('fighter_2_palong');
        $fighter2Lambe = $request->boolean('fighter_2_lambe');
        $fighter2LegColor = (string) $request->query('fighter_2_leg_color', '');

        $request->validate([
            'filter_fighters' => ['sometimes', 'boolean'],
            'fighter_1_palong' => ['sometimes', 'boolean'],
            'fighter_1_lambe' => ['sometimes', 'boolean'],
            'fighter_1_leg_color' => ['nullable', 'in:White,Yellow,Green,Dark'],
            'fighter_2_palong' => ['sometimes', 'boolean'],
            'fighter_2_lambe' => ['sometimes', 'boolean'],
            'fighter_2_leg_color' => ['nullable', 'in:White,Yellow,Green,Dark'],
        ]);

        if (! Schema::hasTable('matches')) {
            return view('matches.index', [
                'matches' => collect(),
                'search' => $search,
                'fighter1' => $fighter1,
                'fighter2' => $fighter2,
                'fighterFiltersActive' => $fighterFiltersActive,
                'fighter1Palong' => $fighter1Palong,
                'fighter1Lambe' => $fighter1Lambe,
                'fighter1LegColor' => $fighter1LegColor,
                'fighter2Palong' => $fighter2Palong,
                'fighter2Lambe' => $fighter2Lambe,
                'fighter2LegColor' => $fighter2LegColor,
                'filteredWinStats' => null,
            ]);
        }

        $filteredMatchesQuery = FightMatch::query()
            ->when($fighter1 !== '', function ($query) use ($fighter1) {
                $query->where('fighter_1', 'like', "%{$fighter1}%");
            })
            ->when($fighter2 !== '', function ($query) use ($fighter2) {
                $query->where('fighter_2', 'like', "%{$fighter2}%");
            })
            ->when($fighterFiltersActive, function ($query) use (
                $fighter1Palong,
                $fighter1Lambe,
                $fighter1LegColor,
                $fighter2Palong,
                $fighter2Lambe,
                $fighter2LegColor,
            ) {
                $query->where('fighter_1_palong', $fighter1Palong)
                    ->where('fighter_1_lambe', $fighter1Lambe)
                    ->where('fighter_2_palong', $fighter2Palong)
                    ->where('fighter_2_lambe', $fighter2Lambe);

                if ($fighter1LegColor !== '') {
                    $query->where('fighter_1_leg_color', $fighter1LegColor);
                }

                if ($fighter2LegColor !== '') {
                    $query->where('fighter_2_leg_color', $fighter2LegColor);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fighter_1', 'like', "%{$search}%")
                        ->orWhere('fighter_2', 'like', "%{$search}%")
                        ->orWhere('winner', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        $hasMatchFilters = $search !== ''
            || $fighter1 !== ''
            || $fighter2 !== ''
            || $fighterFiltersActive;
        $filteredWinStats = null;

        if ($hasMatchFilters) {
            $filteredWinStats = (clone $filteredMatchesQuery)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(winner) = LOWER(fighter_1) THEN 1 ELSE 0 END), 0) as fighter_1_wins')
                ->selectRaw('COALESCE(SUM(CASE WHEN LOWER(winner) = LOWER(fighter_2) THEN 1 ELSE 0 END), 0) as fighter_2_wins')
                ->first();

            $total = (int) $filteredWinStats->total;
            $fighter1Wins = (int) $filteredWinStats->fighter_1_wins;
            $fighter2Wins = (int) $filteredWinStats->fighter_2_wins;
            $filteredWinStats = [
                'total' => $total,
                'fighter_1_wins' => $fighter1Wins,
                'fighter_2_wins' => $fighter2Wins,
                'fighter_1_percentage' => $total > 0 ? round(($fighter1Wins / $total) * 100, 1) : 0,
                'fighter_2_percentage' => $total > 0 ? round(($fighter2Wins / $total) * 100, 1) : 0,
            ];
        }

        $matches = $filteredMatchesQuery
            ->orderByDesc('created_at')
            ->paginate(6)
            ->appends([
                'search' => $search,
                'fighter_1' => $fighter1,
                'fighter_2' => $fighter2,
                'filter_fighters' => $fighterFiltersActive ? 1 : null,
                'fighter_1_palong' => $fighter1Palong ? 1 : 0,
                'fighter_1_lambe' => $fighter1Lambe ? 1 : 0,
                'fighter_1_leg_color' => $fighter1LegColor,
                'fighter_2_palong' => $fighter2Palong ? 1 : 0,
                'fighter_2_lambe' => $fighter2Lambe ? 1 : 0,
                'fighter_2_leg_color' => $fighter2LegColor,
            ]);

        return view('matches.index', compact(
            'matches',
            'search',
            'fighter1',
            'fighter2',
            'fighterFiltersActive',
            'fighter1Palong',
            'fighter1Lambe',
            'fighter1LegColor',
            'fighter2Palong',
            'fighter2Lambe',
            'fighter2LegColor',
            'filteredWinStats',
        ));
    }

    public function create()
    {
        return view('matches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fighter_1' => ['required', 'string', 'max:255'],
            'fighter_2' => ['required', 'string', 'max:255'],
            'fighter_1_palong' => ['sometimes', 'boolean'],
            'fighter_1_lambe' => ['sometimes', 'boolean'],
            'fighter_1_leg_color' => ['nullable', 'in:White,Yellow,Green,Dark'],
            'fighter_2_palong' => ['sometimes', 'boolean'],
            'fighter_2_lambe' => ['sometimes', 'boolean'],
            'fighter_2_leg_color' => ['nullable', 'in:White,Yellow,Green,Dark'],
            'winner' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'cropped_image_data' => ['nullable', 'string'],
        ]);

        $imagePath = $this->saveImageFromRequest($request);

        FightMatch::create([
            'fighter_1' => $validated['fighter_1'],
            'fighter_2' => $validated['fighter_2'],
            'fighter_1_palong' => $request->boolean('fighter_1_palong'),
            'fighter_1_lambe' => $request->boolean('fighter_1_lambe'),
            'fighter_1_leg_color' => $validated['fighter_1_leg_color'] ?? null,
            'fighter_2_palong' => $request->boolean('fighter_2_palong'),
            'fighter_2_lambe' => $request->boolean('fighter_2_lambe'),
            'fighter_2_leg_color' => $validated['fighter_2_leg_color'] ?? null,
            'winner' => $validated['winner'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
        ]);

        return redirect()->route('matches.index')->with('success', 'Match saved successfully.');
    }

    public function edit(FightMatch $match)
    {
        return view('matches.edit', compact('match'));
    }

    public function update(Request $request, FightMatch $match)
    {
        $validated = $request->validate([
            'fighter_1' => ['required', 'string', 'max:255'],
            'fighter_2' => ['required', 'string', 'max:255'],
            'fighter_1_palong' => ['sometimes', 'boolean'],
            'fighter_1_lambe' => ['sometimes', 'boolean'],
            'fighter_1_leg_color' => ['nullable', 'in:White,Yellow,Green,Dark'],
            'fighter_2_palong' => ['sometimes', 'boolean'],
            'fighter_2_lambe' => ['sometimes', 'boolean'],
            'fighter_2_leg_color' => ['nullable', 'in:White,Yellow,Green,Dark'],
            'winner' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'cropped_image_data' => ['nullable', 'string'],
        ]);

        $imagePath = $this->saveImageFromRequest($request, $match->image_path);

        if ($request->hasFile('image') || $request->filled('cropped_image_data')) {
            $oldImagePath = public_path($match->image_path ?? '');
            if ($match->image_path && File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }
        }

        $match->fighter_1 = $validated['fighter_1'];
        $match->fighter_2 = $validated['fighter_2'];
        $match->fighter_1_palong = $request->boolean('fighter_1_palong');
        $match->fighter_1_lambe = $request->boolean('fighter_1_lambe');
        $match->fighter_1_leg_color = $validated['fighter_1_leg_color'] ?? null;
        $match->fighter_2_palong = $request->boolean('fighter_2_palong');
        $match->fighter_2_lambe = $request->boolean('fighter_2_lambe');
        $match->fighter_2_leg_color = $validated['fighter_2_leg_color'] ?? null;
        $match->winner = $validated['winner'];
        $match->description = $validated['description'];
        $match->image_path = $imagePath ?? $match->image_path;
        $match->save();

        return redirect()->route('matches.index')->with('success', 'Match updated successfully.');
    }

    public function destroy(FightMatch $match)
    {
        if ($match->image_path) {
            $imagePath = public_path($match->image_path);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $match->delete();

        return redirect()->route('matches.index')->with('success', 'Match deleted successfully.');
    }

    protected function saveImageFromRequest(Request $request, ?string $existingPath = null): ?string
    {
        if ($request->filled('cropped_image_data')) {
            $data = $request->input('cropped_image_data');
            $data = preg_replace('/^data:image\/(png|jpeg|jpg|gif|webp);base64,/', '', $data, 1);
            $decoded = base64_decode((string) $data, true);

            if ($decoded !== false) {
                $directory = public_path('images/matches');

                if (! File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true, true);
                }

                $fileName = time().'_'.uniqid().'.jpg';
                $targetPath = $directory.DIRECTORY_SEPARATOR.$fileName;
                file_put_contents($targetPath, $decoded);

                return 'images/matches/'.$fileName;
            }
        }

        if ($request->hasFile('image')) {
            $directory = public_path('images/matches');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            $fileName = time().'_'.uniqid().'.'.strtolower($request->file('image')->getClientOriginalExtension() ?: 'jpg');
            $request->file('image')->move($directory, $fileName);

            return 'images/matches/'.$fileName;
        }

        return $existingPath;
    }
}
