<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    private const TYPES = ['studio', 'one_bedroom', 'two_bedroom', 'three_bedroom', 'four_bedroom', 'five_plus_bedroom'];

    private const SORTS = ['newest', 'price_asc', 'price_desc', 'surface_desc'];

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $rooms = $this->query($filters)->paginate(12)->withQueryString();

        $mapBuildings = Building::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('rooms', fn ($q) => $q->where('status', 'available'))
            ->with(['rooms' => fn ($q) => $q->where('status', 'available')->select('id', 'building_id', 'title', 'price_per_month', 'type')])
            ->select('id', 'name', 'street', 'house_number', 'postal_code', 'city', 'latitude', 'longitude')
            ->get()
            ->map(fn (Building $b) => [
                'lat' => (float) $b->latitude,
                'lng' => (float) $b->longitude,
                'name' => $b->name,
                'address' => "{$b->street} {$b->house_number}, {$b->postal_code} {$b->city}",
                'rooms' => $b->rooms->map(fn (Room $r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'price' => (float) $r->price_per_month,
                    'url' => route('rooms.show', $r),
                ])->values(),
            ]);

        return view('rooms.index', [
            'rooms' => $rooms,
            'filters' => $filters,
            'types' => self::TYPES,
            'mapBuildings' => $mapBuildings,
        ]);
    }

    public function show(Room $room): View
    {
        $room->load(['building', 'media', 'facilities', 'costTypes']);

        return view('rooms.show', compact('room'));
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
            'sort' => in_array($request->input('sort'), self::SORTS, true) ? $request->input('sort') : 'newest',
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
            ->when($filters['sort'] === 'price_asc', fn ($query) => $query->orderBy('price_per_month'))
            ->when($filters['sort'] === 'price_desc', fn ($query) => $query->orderByDesc('price_per_month'))
            ->when($filters['sort'] === 'surface_desc', fn ($query) => $query->orderByDesc('surface_m2'))
            ->when($filters['sort'] === 'newest', fn ($query) => $query->latest());
    }
}
