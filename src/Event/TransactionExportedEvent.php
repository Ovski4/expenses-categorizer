<?php

namespace App\Event;

use App\Entity\Transaction;
use Symfony\Contracts\EventDispatcher\Event;

class TransactionExportedEvent extends Event
{
    public const NAME = 'transaction.exported';

    /**
     * @var array<string, mixed>
     */
    private array $response;

    private Transaction $transaction;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(Transaction $transaction, array $response)
    {
        $this->transaction = $transaction;
        $this->response = $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
