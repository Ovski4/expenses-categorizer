<?php

namespace App\Entity;

abstract class Operator
{
    const EQUALS = 'equals';
    const GREATER_THAN_OR_EQUAL = 'greater than or equal';
    const LOWER_THAN_OR_EQUAL = 'lower than or equal';

    public static function getAll(): array {
        return [
            self::EQUALS,
            self::GREATER_THAN_OR_EQUAL,
            self::LOWER_THAN_OR_EQUAL
        ];
    }
}
