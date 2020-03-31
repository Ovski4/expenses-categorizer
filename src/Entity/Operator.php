<?php

namespace App\Entity;

abstract class Operator
{
    const EQUALS = 'equals';

    public static function getAll(): array {
        return [
            self::EQUALS
        ];
    }

}
