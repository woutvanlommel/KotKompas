<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Room;
use App\Services\KotScoreService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const TYPES = ['studio', 'one_bedroom', 'two_bedroom', 'three_bedroom', 'four_bedroom', 'five_plus_bedroom'];

    private const SORTS = ['newest', 'price_asc', 'price_desc', 'surface_desc', 'score'];

    private const SCORE_MINIMUMS = ['3', '3.5', '4', '4.5'];

    private const VIEWS = ['grid', 'list', 'map'];

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $rooms = $this->query($filters)->paginate(12)->withQueryString();

        // Map data: same filters as the room list, but without pagination.
        // Group per building so the popup can show multiple rooms.
        $filteredRooms = $this->query($filters)
            ->with(['building' => fn ($q) => $q->whereNotNull('latitude')->whereNotNull('longitude')])
            ->get()
            ->filter(fn (Room $r) => $r->building && $r->building->latitude && $r->building->longitude);

        $mapBuildings = $filteredRooms
            ->groupBy('building_id')
            ->map(function ($rooms) {
                $building = $rooms->first()->building;

                return [
                    'lat' => (float) $building->latitude,
                    'lng' => (float) $building->longitude,
                    'name' => $building->name,
                    'address' => "{$building->street} {$building->house_number}, {$building->postal_code} {$building->city}",
                    'rooms' => $rooms->map(fn (Room $r) => [
                        'id' => $r->id,
                        'title' => $r->title ?? '',
                        'price' => (float) $r->price_per_month,
                        'url' => route('rooms.show', $r),
                    ])->values(),
                ];
            })
            ->values();

        return view('rooms.index', [
            'rooms' => $rooms,
            'filters' => $filters,
            'types' => self::TYPES,
            'mapBuildings' => $mapBuildings,
        ]);
    }

    /**
     * Zoeksuggesties terwijl de gebruiker typt: steden (met aantal beschikbare
     * koten) en kot-titels. Alleen beschikbare koten tellen mee.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query->all(), [
            'q' => ['required', 'string', 'min:1', 'max:60'],
        ]);

        if ($validator->fails()) {
            return response()->json(['suggestions' => [], 'errors' => $validator->errors()], 422);
        }

        $q = trim(strip_tags($validator->validated()['q']));

        if ($q === '') {
            return response()->json(['suggestions' => []]);
        }

        $cities = Building::query()
            ->whereHas('rooms', fn (Builder $query) => $query->where('status', 'available'))
            ->where(fn (Builder $query) => $query
                ->where('city', 'like', "{$q}%")
                ->orWhere('postal_code', 'like', "{$q}%"))
            ->withCount(['rooms as available_count' => fn (Builder $query) => $query->where('status', 'available')])
            ->get()
            ->groupBy('city')
            ->map(fn ($buildings, $city) => [
                'type' => 'stad',
                'label' => $city,
                'detail' => $buildings->sum('available_count').' beschikbaar',
                'url' => route('rooms.index', ['q' => $city]),
            ])
            ->values()
            ->take(4);

        $rooms = Room::query()
            ->where('status', 'available')
            ->where('title', 'like', "%{$q}%")
            ->with('building')
            ->limit(4)
            ->get()
            ->map(fn (Room $room) => [
                'type' => 'kot',
                'label' => $room->title,
                'detail' => $room->building->city.' · € '.number_format((float) $room->price_per_month, 0, ',', '.').'/maand',
                'url' => route('rooms.show', $room),
            ]);

        return response()->json([
            'suggestions' => $cities->concat($rooms)->values(),
        ]);
    }

    public function show(Room $room, KotScoreService $kotScoreService): View
    {
        $room->load(['building', 'media', 'facilities', 'costTypes']);

        $scoreBreakdown = $room->reviews_count > 0 ? $kotScoreService->criteriaBreakdown($room) : null;

        return view('rooms.show', compact('room', 'scoreBreakdown'));
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->string('q')) ?: null,
            'type' => in_array($request->input('type'), self::TYPES, true) ? $request->input('type') : null,
            'price_min' => $request->integer('price_min') ?: null,
            'price_max' => $request->integer('price_max') ?: null,
            'surface_min' => $request->integer('surface_min') ?: null,
            'furnished' => $request->boolean('furnished') ?: null,
            'score_min' => in_array($request->input('score_min'), self::SCORE_MINIMUMS, true)
                ? (float) $request->input('score_min')
                : null,
            'sort' => in_array($request->input('sort'), self::SORTS, true) ? $request->input('sort') : 'newest',
            'view' => in_array($request->input('view'), self::VIEWS, true) ? $request->input('view') : 'grid',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Room>
     */
    private function query(array $filters)
    {
        return Room::query()
            ->where('status', 'available')
            ->with(['building', 'media'])
            ->when($filters['q'], fn ($query, $q) => $query
                ->where(fn ($sub) => $sub
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('building', fn ($b) => $b
                        ->where('city', 'like', "%{$q}%")
                        ->orWhere('postal_code', 'like', "%{$q}%"))))
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->when($filters['price_min'], fn ($query, $min) => $query->where('price_per_month', '>=', $min))
            ->when($filters['price_max'], fn ($query, $max) => $query->where('price_per_month', '<=', $max))
            ->when($filters['surface_min'], fn ($query, $min) => $query->where('surface_m2', '>=', $min))
            ->when($filters['furnished'], fn ($query) => $query->where('is_furnished', true))
            // Filteren gebeurt op de getoonde score; koten zonder
            // beoordelingen (score null) vallen dan vanzelf af.
            ->when($filters['score_min'], fn ($query, $min) => $query->where('score', '>=', $min))
            ->when($filters['sort'] === 'price_asc', fn ($query) => $query->orderBy('price_per_month'))
            ->when($filters['sort'] === 'price_desc', fn ($query) => $query->orderByDesc('price_per_month'))
            ->when($filters['sort'] === 'surface_desc', fn ($query) => $query->orderByDesc('surface_m2'))
            // Sorteren gaat op score_bayesian (nooit getoond): één verse
            // 5-sterrenreview komt zo niet boven consistent goede koten.
            // Onbeoordeelde koten sluiten achteraan aan, nieuwste eerst.
            ->when($filters['sort'] === 'score', fn ($query) => $query
                ->orderByRaw('case when score_bayesian is null then 1 else 0 end')
                ->orderByDesc('score_bayesian')
                ->latest())
            ->when($filters['sort'] === 'newest', fn ($query) => $query->latest());
    }
}
