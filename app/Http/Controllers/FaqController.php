<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        return view('faq', ['faqs' => $faqs]);
    }
}
