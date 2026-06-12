<?php

namespace App\Http\Controllers;

use App\Models\FaqCategory;
use App\Models\Room;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Featured = best reviewed, ranked on score_bayesian (never shown):
        // a single fresh 5-star review does not outrank consistently good
        // rooms. Unreviewed rooms fill the remaining slots, newest first.
        $featuredRooms = Room::query()
            ->where('status', 'available')
            ->with(['building', 'media'])
            ->orderByRaw('case when score_bayesian is null then 1 else 0 end')
            ->orderByDesc('score_bayesian')
            ->latest()
            ->take(8)
            ->get();

        $faqCategories = FaqCategory::query()
            ->where('is_active', true)
            ->whereHas('faqs', fn ($q) => $q->where('is_active', true))
            ->with(['faqs' => fn ($q) => $q->where('is_active', true)->orderBy('sort')->limit(2)])
            ->orderBy('sort')
            ->take(2)
            ->get();

        return view('home', [
            'featuredRooms' => $featuredRooms,
            'faqCategories' => $faqCategories,
        ]);
    }
}
