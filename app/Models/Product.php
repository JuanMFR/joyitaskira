<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'code',
        'price',
        'description',
        'status',
        'is_featured',
    ];

    protected $attributes = [
        'status' => 'available',
        'is_featured' => false,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Borra las fotos una por una para que también se eliminen los archivos.
        static::deleting(fn (self $product) => $product->images()->get()->each->delete());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    /** Productos que se muestran en la web pública (todo menos "baja"). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', '!=', ProductStatus::Discontinued);
    }

    /** Datos que usa el carrito del navegador. */
    public function toCartArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'priceLabel' => Money::format($this->price),
            'image' => $this->mainImage?->url,
            'url' => route('products.show', $this),
            'purchasable' => $this->status->isPurchasable(),
            'statusLabel' => $this->status->getLabel(),
        ];
    }

    /**
     * Garantiza que haya exactamente una foto principal:
     * si no hay ninguna marcada usa la primera; si hay varias deja la primera marcada.
     */
    public function normalizeMainImage(): void
    {
        $images = $this->images()->get();

        if ($images->isEmpty()) {
            return;
        }

        $main = $images->firstWhere('is_main', true) ?? $images->first();

        $this->images()->whereKeyNot($main->getKey())->where('is_main', true)->update(['is_main' => false]);

        if (! $main->is_main) {
            $main->update(['is_main' => true]);
        }
    }
}
