<?php

namespace App\Services;

use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\Transaction;
use App\Entity\SubCategoryTransactionRule;
use App\Exception\TransactionMatchesMultipleRulesException;
use App\Repository\SubCategoryTransactionRuleRepository;

class RuleChecker
{
    private $repository;

    private $rules;

    public function __construct(SubCategoryTransactionRuleRepository $repository)
    {
        $this->repository = $repository;
        $this->setRules();
    }

    public function setRules()
    {
        $this->rules = $this->repository->findAll()
        ;
    }

    public function getMatchingSubCategory(Transaction $transaction) : ?SubCategory
    {
        $foundSubCategory = null;
        foreach ($this->rules as $rule) {
            $ruleType = $rule->getSubCategory()->getTransactionType();

            $amountMatches = true;
            if ($rule->getAmount() !== null) {
                if (
                    $rule->getOperator() == Operator::EQUALS &&
                    $rule->getAmount() !== $transaction->getAmount()
                ) {
                    $amountMatches = false;
                }
            }

            if (
                $amountMatches &&
                $transaction->getType() == $ruleType &&
                strpos($transaction->getLabel(), $rule->getContains()) !== false
            ) {
                if ($foundSubCategory != null) {
                    throw new TransactionMatchesMultipleRulesException($transaction);
                }

                $foundSubCategory = $rule->getSubCategory();
            }
        }

        return $foundSubCategory;
    }
}
