<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionsExportingEvent extends Event
{
    public const NAME = 'transactions.exporting';

    protected int $transactionCount;

    public function __construct(int $transactionCount)
    {
        $this->transactionCount = $transactionCount;
    }

    public function getTransactionCount(): int
    {
        return $this->transactionCount;
    }
}
