<?php

namespace App\Services;

use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\Transaction;
use App\Repository\SubCategoryTransactionRuleRepository;

class RuleChecker
{
    private $rules;

    public function __construct(SubCategoryTransactionRuleRepository $subCategoryTransactionRuleRepository)
    {
        $this->rules = $subCategoryTransactionRuleRepository->findAll();
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
                    throw new \Exception(sprintf(
                        'Multiple sub categories found for transaction %s',
                        $transaction
                    ));
                }

                $foundSubCategory = $rule->getSubCategory();
            }
        }

        return $foundSubCategory;
    }
}
