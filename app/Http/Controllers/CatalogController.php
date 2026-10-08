<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public const SORTS = [
        'recientes' => 'Más recientes',
        'menor-precio' => 'Menor precio',
        'mayor-precio' => 'Mayor precio',
        'nombre' => 'Nombre',
    ];

    public function index(Request $request): View
    {
        $categories = Category::active()->ordered()->get();
        $category = $categories->firstWhere('slug', $request->query('categoria'));
        $search = trim((string) $request->query('q'));
        $sort = array_key_exists($request->query('orden'), self::SORTS) ? $request->query('orden') : 'recientes';

        $products = Product::visible()
            ->with('mainImage')
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            // Los disponibles primero, los sin stock al final.
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [ProductStatus::Available->value])
            ->when($sort === 'recientes', fn ($q) => $q->latest())
            ->when($sort === 'menor-precio', fn ($q) => $q->orderBy('price'))
            ->when($sort === 'mayor-precio', fn ($q) => $q->orderByDesc('price'))
            ->when($sort === 'nombre', fn ($q) => $q->orderBy('name'))
            ->paginate(24)
            ->withQueryString();

        return view('store.catalog', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
            'sorts' => self::SORTS,
        ]);
    }
}
