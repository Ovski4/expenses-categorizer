<?php

namespace App\Entity;

abstract class Currency
{
    const EUR = 'EUR';
    const NZD = 'NZD';
    const CAD = 'CAD';

    public static function getAll(): array {
        return [
            self::EUR,
            self::NZD,
            self::CAD,
        ];
    }

}
