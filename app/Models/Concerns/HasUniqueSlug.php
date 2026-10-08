<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Genera un slug único a partir de otro atributo (por defecto "name")
 * al crear el registro, si no se cargó uno a mano.
 */
trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::creating(function ($model) {
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug($model->{$model->slugSource()});
            }
        });
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function generateUniqueSlug(?string $value): string
    {
        $base = Str::slug((string) $value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (static::query()->where('slug', $slug)->whereKeyNot($this->getKey())->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
