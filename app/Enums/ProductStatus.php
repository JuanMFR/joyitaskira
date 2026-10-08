<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasColor, HasLabel
{
    case Available = 'available';
    case OutOfStock = 'out_of_stock';
    case Discontinued = 'discontinued';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::OutOfStock => 'Agotado',
            self::Discontinued => 'Dado de baja',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::OutOfStock => 'warning',
            self::Discontinued => 'gray',
        };
    }

    /** Se muestra en la web pública (los dados de baja no). */
    public function isVisible(): bool
    {
        return $this !== self::Discontinued;
    }

    /** Se puede agregar al carrito. */
    public function isPurchasable(): bool
    {
        return $this === self::Available;
    }
}
