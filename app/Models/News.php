<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Noticias del carrusel de inicio. Si tienen link, toda la noticia es clickeable;
 * si no, solo se muestra la información.
 */
class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'image',
        'title',
        'description',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (self $news) => Storage::disk('public')->delete($news->image));

        static::updated(function (self $news) {
            if ($news->wasChanged('image') && $news->getOriginal('image')) {
                Storage::disk('public')->delete($news->getOriginal('image'));
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image);
    }

    /** Los links a otras webs se abren en una pestaña nueva. */
    public function isExternalLink(): bool
    {
        return str_starts_with((string) $this->link_url, 'http');
    }
}
