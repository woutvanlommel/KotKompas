<?php

namespace App\Http\Controllers;

use App\Models\FaqCategory;
use App\Models\Room;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredRooms = Room::query()
            ->where('status', 'available')
            ->with(['building', 'media'])
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
