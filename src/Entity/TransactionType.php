<?php

namespace App\Entity;

abstract class TransactionType
{
    public const EXPENSES = 'Expenses';
    public const REVENUES = 'Revenues';

    public static function getAll(): array
    {
        return [
            self::EXPENSES,
            self::REVENUES,
        ];
    }
}
