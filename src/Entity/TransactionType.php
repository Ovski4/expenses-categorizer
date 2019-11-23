<?php

namespace App\Entity;

abstract class TransactionType
{
    const EXPENSES = 'Expenses';
    const REVENUES = 'Revenues';

    public static function getAll(): array {
        return [
            self::EXPENSES,
            self::REVENUES
        ];
    }

}
