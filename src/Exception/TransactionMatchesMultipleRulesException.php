<?php

namespace App\Exception;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\Transaction;

class TransactionMatchesMultipleRulesException extends \Exception
{
    private Transaction $transaction;

    /**
     * @var array<int, SubCategoryTransactionRule>
     */
    private array $rules;

    /**
     * @param array<int, SubCategoryTransactionRule> $rules
     */
    public function __construct(Transaction $transaction, array $rules)
    {
        $this->transaction = $transaction;
        $this->rules = $rules;

        parent::__construct('Multiple rules are matching the transaction');
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @return SubCategoryTransactionRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
