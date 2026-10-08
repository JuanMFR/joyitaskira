<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\News;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Datos de ejemplo SOLO para desarrollo local (ver cómo queda la tienda).
 * Uso: php artisan db:seed --class=DemoSeeder
 * Nunca correr en producción.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder no se corre en producción.');

            return;
        }

        $this->call(DatabaseSeeder::class);

        $catalog = [
            'Aros' => [['Aros Luna', 12500], ['Argollas Doradas', 9800], ['Aros Estrella', 11200]],
            'Collares' => [['Collar Constelación', 18900], ['Collar Perla Fina', 21500], ['Choker Brillo', 14300]],
            'Anillos' => [['Anillo Sol', 10500], ['Anillo Infinito', 12900]],
            'Pulseras' => [['Pulsera Eslabón', 13400], ['Pulsera Dije Corazón', 15600]],
        ];

        $palette = [[220, 234, 245], [238, 223, 189], [243, 237, 225], [205, 222, 238]];
        $n = 0;

        foreach ($catalog as $categoryName => $items) {
            $category = Category::firstWhere('name', $categoryName);

            foreach ($items as [$name, $price]) {
                $product = Product::firstOrCreate(['name' => $name], [
                    'category_id' => $category?->id,
                    'price' => $price,
                    'description' => "Pieza delicada de la colección Kira.\n\nIdeal para usar todos los días o para regalar.",
                    'status' => $n === 4 ? ProductStatus::OutOfStock : ProductStatus::Available,
                    'is_featured' => $n % 2 === 0,
                ]);

                if ($product->images()->doesntExist()) {
                    foreach ([0, 1] as $k) {
                        $product->images()->create([
                            'path' => $this->placeholder("products/demo-{$product->id}-{$k}.jpg", 1200, 1500, $palette[($n + $k) % 4]),
                            'is_main' => $k === 0,
                            'sort_order' => $k,
                        ]);
                    }
                }
                $n++;
            }
        }

        if (News::doesntExist()) {
            News::create(['image' => $this->placeholder('news/demo-1.jpg', 1920, 800, [205, 222, 238]), 'title' => 'Nueva colección', 'description' => 'Aros, collares y anillos de plata para todos los días.', 'link_url' => '/catalogo', 'sort_order' => 1]);
            News::create(['image' => $this->placeholder('news/demo-2.jpg', 1920, 800, [238, 223, 189]), 'title' => '¡Abrimos la tienda online!', 'description' => 'Armá tu carrito y mandanos el pedido por WhatsApp.', 'sort_order' => 2]);
        }
    }

    /** Genera una imagen de relleno con degradé suave y el logo al centro. */
    private function placeholder(string $path, int $w, int $h, array $rgb): string
    {
        $im = imagecreatetruecolor($w, $h);
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $c = imagecolorallocate($im, ...array_map(fn ($v) => (int) min(255, $v + 30 * $t), $rgb));
            imageline($im, 0, $y, $w, $y, $c);
        }

        $logo = imagecreatefrompng(public_path('images/logo-mark.png'));
        $size = (int) (min($w, $h) * 0.35);
        imagecopyresampled($im, $logo, (int) (($w - $size) / 2), (int) (($h - $size) / 2), 0, 0, $size, $size, imagesx($logo), imagesy($logo));

        ob_start();
        imagejpeg($im, null, 82);
        Storage::disk('public')->put($path, ob_get_clean());

        return $path;
    }
}
