<?php

namespace App\Event;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\Transaction;
use Symfony\Contracts\EventDispatcher\Event;

class TransactionMatchesMultipleRulesEvent extends Event
{
    public const NAME = 'transaction.matches_multiple_rules';

    protected $transaction;
    protected $rules;

    public function __construct(Transaction $transaction, array $rules)
    {
        $this->rules = $rules;
        $this->transaction = $transaction;
    }

    /**
     * @return SubCategoryTransactionRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
