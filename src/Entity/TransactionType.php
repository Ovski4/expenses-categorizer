<?php

namespace App\Entity;

abstract class TransactionType
{
    public const EXPENSES = 'Expenses';
    public const REVENUES = 'Revenues';

    /**
     * @return array<int, string>
     */
    public static function getAll(): array
    {
        return [
            self::EXPENSES,
            self::REVENUES,
        ];
    }
}
