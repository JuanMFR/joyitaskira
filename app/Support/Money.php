<?php

namespace App\Support;

class Money
{
    /** Formato argentino: $ 12.500 (o $ 12.500,50 si tiene centavos). */
    public static function format(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = floor($amount) == $amount ? 0 : 2;

        return '$ '.number_format($amount, $decimals, ',', '.');
    }
}
