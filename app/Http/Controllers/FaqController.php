<?php

namespace App\Http\Controllers;

use App\Models\FaqCategory;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $categories = FaqCategory::query()
            ->where('is_active', true)
            ->whereHas('faqs', fn ($q) => $q->where('is_active', true))
            ->with(['faqs' => fn ($q) => $q->where('is_active', true)->orderBy('sort')])
            ->orderBy('sort')
            ->get();

        return view('faq', ['categories' => $categories]);
    }
}
