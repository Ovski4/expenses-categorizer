<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionsExportedEvent extends Event
{
    public const NAME = 'transactions.exported';
}
