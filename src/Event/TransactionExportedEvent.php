<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TransactionExportedEvent extends Event
{
    public const NAME = 'transaction.exported';

    protected $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
