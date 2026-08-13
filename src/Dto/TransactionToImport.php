<?php

namespace App\Dto;

use App\Entity\Transaction;

/**
 * A transaction parsed from a statement file, waiting to be imported,
 * along with whether an identical one is already stored.
 */
final readonly class TransactionToImport
{
    public function __construct(
        public Transaction $transaction,
        public bool $alreadyExists,
    ) {
    }
}
