<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'is_main',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Borra el archivo cuando se elimina la foto o se reemplaza por otra.
        static::deleted(fn (self $image) => Storage::disk('public')->delete($image->path));

        static::updated(function (self $image) {
            if ($image->wasChanged('path') && $image->getOriginal('path')) {
                Storage::disk('public')->delete($image->getOriginal('path'));
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
