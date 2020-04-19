<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionsCategorizedEvent extends Event
{
    public const NAME = 'transactions.categorized';
}
