<?php

namespace App\Exception;

use App\Entity\Transaction;

class TransactionMatchesMultipleRulesException extends \Exception
{
    private $transaction;

    private $rules;

    public function __construct(Transaction $transaction, array $rules)
    {
        $this->transaction = $transaction;
        $this->rules = $rules;

        parent::__construct('Multiple rules are matching the transaction');
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    public function getRules()
    {
        return $this->rules;
    }
}
