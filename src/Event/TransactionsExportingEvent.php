<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionsExportingEvent extends Event
{
    public const NAME = 'transactions.exporting';

    protected $transactionCount;

    public function __construct(int $transactionCount)
    {
        $this->transactionCount = $transactionCount;
    }

    public function getTransactionCount()
    {
        return $this->transactionCount;
    }
}
