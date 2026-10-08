<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Product::visible()
            ->where('is_featured', true)
            ->with('mainImage')
            ->latest()
            ->take(8)
            ->get();

        // Si todavía no hay destacados, mostramos lo último cargado.
        if ($featured->isEmpty()) {
            $featured = Product::visible()->with('mainImage')->latest()->take(8)->get();
        }

        return view('store.home', [
            'news' => News::active()->get(),
            'featured' => $featured,
            'categories' => Category::active()->ordered()
                ->withCount(['products' => fn ($q) => $q->visible()])
                ->get()
                ->where('products_count', '>', 0),
        ]);
    }
}
