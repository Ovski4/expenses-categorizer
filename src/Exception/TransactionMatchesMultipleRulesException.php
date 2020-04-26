<?php

namespace App\Exception;

use App\Entity\Transaction;

class TransactionMatchesMultipleRulesException extends \Exception
{
    private $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;

        parent::__construct('Multiple sub categories found for transaction "%transaction%"');
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
}
