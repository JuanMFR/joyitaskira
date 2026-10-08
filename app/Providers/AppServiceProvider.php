<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Datos comunes del layout de la tienda (configuración y categorías del menú).
        View::composer('components.layouts.store', function ($view) {
            $view->with([
                'settings' => Setting::allCached(),
                'navCategories' => Category::active()->ordered()->get(['id', 'name', 'slug']),
            ]);
        });
    }
}
