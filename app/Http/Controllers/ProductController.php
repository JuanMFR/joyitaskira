<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->status->isVisible(), 404);

        $product->load(['images', 'category']);

        $related = Product::visible()
            ->with('mainImage')
            ->whereKeyNot($product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('store.product', compact('product', 'related'));
    }

    /**
     * Datos actuales de los productos del carrito (precio, stock, foto).
     * El carrito vive en el navegador; esto evita mandar precios viejos por WhatsApp.
     */
    public function cartData(Request $request): JsonResponse
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->take(100);

        $products = Product::with('mainImage')
            ->visible()
            ->whereKey($ids)
            ->get()
            ->map(fn (Product $p) => $p->toCartArray());

        return response()->json($products->values());
    }
}
