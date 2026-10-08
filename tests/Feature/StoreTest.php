<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\News;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_home_renders_featured_products_and_news(): void
    {
        Product::create(['name' => 'Aros Luna', 'price' => 12500, 'is_featured' => true]);
        Product::create(['name' => 'Oculto', 'price' => 1, 'is_featured' => true, 'status' => ProductStatus::Discontinued]);
        News::create(['image' => 'news/a.jpg', 'title' => 'Abrimos la tienda', 'description' => 'Ya podés comprar online']);
        News::create(['image' => 'news/b.jpg', 'title' => 'Borrador', 'description' => 'x', 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Aros Luna')
            ->assertSee('$ 12.500')
            ->assertDontSee('Oculto')
            ->assertSee('Abrimos la tienda')
            ->assertSee('Ya podés comprar online')
            ->assertDontSee('Borrador');
    }

    public function test_news_is_clickable_only_when_it_has_a_link(): void
    {
        News::create(['image' => 'news/a.jpg', 'title' => 'Con link interno', 'description' => 'd', 'link_url' => '/catalogo?categoria=aros', 'sort_order' => 1]);
        News::create(['image' => 'news/b.jpg', 'title' => 'Con link externo', 'description' => 'd', 'link_url' => 'https://instagram.com/kira', 'sort_order' => 2]);
        News::create(['image' => 'news/c.jpg', 'title' => 'Sin link', 'description' => 'd', 'sort_order' => 3]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="/catalogo?categoria=aros" class="absolute inset-0" aria-label="Con link interno"', $html);
        $this->assertMatchesRegularExpression('#href="https://instagram.com/kira"[^>]*aria-label="Con link externo"\s+target="_blank"#', $html);
        $this->assertStringNotContainsString('aria-label="Sin link"', $html);
    }

    public function test_catalog_filters_by_category_and_search(): void
    {
        $aros = Category::create(['name' => 'Aros']);
        $collares = Category::create(['name' => 'Collares']);
        Product::create(['name' => 'Aros Luna', 'price' => 1, 'category_id' => $aros->id]);
        Product::create(['name' => 'Collar Estrella', 'price' => 1, 'category_id' => $collares->id]);
        Product::create(['name' => 'Aros Baja', 'price' => 1, 'category_id' => $aros->id, 'status' => ProductStatus::Discontinued]);

        $this->get('/catalogo?categoria=aros')->assertOk()->assertSee('Aros Luna')->assertDontSee('Collar Estrella')->assertDontSee('Aros Baja');
        $this->get('/catalogo?q=estrella')->assertOk()->assertSee('Collar Estrella')->assertDontSee('Aros Luna');
    }

    public function test_product_page_and_status_rules(): void
    {
        $available = Product::create(['name' => 'Anillo Sol', 'price' => 10500, 'description' => 'Precioso']);
        $outOfStock = Product::create(['name' => 'Anillo Luna', 'price' => 1, 'status' => ProductStatus::OutOfStock]);
        $discontinued = Product::create(['name' => 'Anillo Viejo', 'price' => 1, 'status' => ProductStatus::Discontinued]);

        $this->get("/producto/{$available->slug}")->assertOk()->assertSee('Anillo Sol')->assertSee('Precioso')->assertSee('Agregar al carrito');
        $this->get("/producto/{$outOfStock->slug}")->assertOk()->assertSee('Agotado')->assertDontSee('Pieza única');
        $this->get("/producto/{$discontinued->slug}")->assertNotFound();
    }

    public function test_cart_data_returns_current_info_and_skips_discontinued(): void
    {
        $a = Product::create(['name' => 'A', 'price' => 100]);
        $b = Product::create(['name' => 'B', 'price' => 200, 'status' => ProductStatus::OutOfStock]);
        $c = Product::create(['name' => 'C', 'price' => 300, 'status' => ProductStatus::Discontinued]);

        $this->getJson("/carrito/productos?ids={$a->id},{$b->id},{$c->id},abc")
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $a->id, 'price' => 100, 'purchasable' => true])
            ->assertJsonFragment(['id' => $b->id, 'purchasable' => false, 'statusLabel' => 'Agotado']);
    }

    public function test_whatsapp_number_and_announcement_are_shown_when_configured(): void
    {
        $this->get('/')->assertOk()->assertDontSee('wa.me/');

        Setting::setMany(['whatsapp_number' => '5491122334455', 'announcement' => 'Envíos a todo el país']);

        $this->get('/')->assertOk()->assertSee('wa.me/5491122334455')->assertSee('Envíos a todo el país');
    }

    public function test_money_format(): void
    {
        $this->assertSame('$ 12.500', Money::format(12500));
        $this->assertSame('$ 12.500,50', Money::format('12500.50'));
        $this->assertSame('$ 0', Money::format(null));
    }
}
