<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Filament\Pages\Settings;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\News;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Aros Luna', 'price' => 12000]);
        $category = Category::create(['name' => 'Aros']);

        $pages = [
            '/admin',
            '/admin/products',
            '/admin/products/create',
            "/admin/products/{$product->id}/edit",
            '/admin/categories',
            "/admin/categories/{$category->id}/edit",
            '/admin/news',
            '/admin/news/create',
            '/admin/users',
            "/admin/users/{$user->id}/edit",
            '/admin/settings',
        ];

        foreach ($pages as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_product_can_be_created_from_panel_with_photos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Aros']);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Aros Luna',
                'code' => 'AR-001',
                'price' => 12500.50,
                'category_id' => $category->id,
                'status' => ProductStatus::OutOfStock->value,
                'images' => [
                    ['path' => [UploadedFile::fake()->image('a.jpg')], 'is_main' => false],
                    ['path' => [UploadedFile::fake()->image('b.jpg')], 'is_main' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::firstWhere('code', 'AR-001');
        $this->assertNotNull($product);
        $this->assertSame(ProductStatus::OutOfStock, $product->status);
        $this->assertSame('12500.50', $product->price);
        $this->assertCount(2, $product->images);
        // Ninguna marcada → queda como principal la primera.
        $this->assertSame($product->images->first()->id, $product->mainImage->id);
        Storage::disk('public')->assertExists($product->mainImage->path);
    }

    public function test_news_can_be_created_and_link_is_validated(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $form = [
            'image' => [UploadedFile::fake()->image('n.jpg', 1920, 800)],
            'title' => 'Nueva colección',
            'description' => 'Llegaron los aros de plata',
        ];

        Livewire::test(CreateNews::class)
            ->fillForm([...$form, 'link_url' => 'javascript:alert(1)'])
            ->call('create')
            ->assertHasFormErrors(['link_url']);

        Livewire::test(CreateNews::class)
            ->fillForm([...$form, 'description' => ''])
            ->call('create')
            ->assertHasFormErrors(['description' => 'required']);

        // Sin link es válido: la noticia solo muestra la información.
        Livewire::test(CreateNews::class)
            ->fillForm($form)
            ->call('create')
            ->assertHasNoFormErrors();

        $news = News::firstWhere('title', 'Nueva colección');
        $this->assertNull($news->link_url);
        $this->assertTrue($news->is_active);
        Storage::disk('public')->assertExists($news->image);
    }

    public function test_settings_are_saved(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Settings::class)
            ->fillForm(['whatsapp_number' => '5491122334455'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('5491122334455', Setting::get('whatsapp_number'));

        Livewire::test(Settings::class)
            ->fillForm(['whatsapp_number' => '+54 11 2233'])
            ->call('save')
            ->assertHasFormErrors(['whatsapp_number']);
    }

    public function test_product_gets_slug_default_status_and_single_main_image(): void
    {
        $product = Product::create(['name' => 'Collar Estrella', 'price' => 15000]);
        $twin = Product::create(['name' => 'Collar Estrella', 'price' => 16000]);

        $this->assertSame('collar-estrella', $product->slug);
        $this->assertSame('collar-estrella-2', $twin->slug);
        $this->assertSame(ProductStatus::Available, $product->fresh()->status);

        $product->images()->createMany([
            ['path' => 'products/a.jpg', 'sort_order' => 1],
            ['path' => 'products/b.jpg', 'sort_order' => 2, 'is_main' => true],
            ['path' => 'products/c.jpg', 'sort_order' => 3, 'is_main' => true],
        ]);
        $product->normalizeMainImage();

        $this->assertSame(1, $product->images()->where('is_main', true)->count());
        $this->assertSame('products/b.jpg', $product->mainImage()->first()->path);
    }

    public function test_discontinued_products_are_not_visible(): void
    {
        Product::create(['name' => 'Visible', 'price' => 1]);
        Product::create(['name' => 'Agotado', 'price' => 1, 'status' => ProductStatus::OutOfStock]);
        Product::create(['name' => 'Baja', 'price' => 1, 'status' => ProductStatus::Discontinued]);

        $this->assertEqualsCanonicalizing(['Visible', 'Agotado'], Product::visible()->pluck('name')->all());
    }
}
