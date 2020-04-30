<?php

namespace App\Event;

use App\Entity\Transaction;
use Symfony\Contracts\EventDispatcher\Event;

class TransactionExportedEvent extends Event
{
    public const NAME = 'transaction.exported';

    private $response;

    private $transaction;

    public function __construct(Transaction $transaction, array $response)
    {
        $this->transaction = $transaction;
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
}
