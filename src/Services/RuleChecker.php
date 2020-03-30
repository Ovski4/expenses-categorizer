<?php

namespace App\Services;

use App\Entity\SubCategory;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\SubCategoryTransactionRuleRepository;

class RuleChecker
{
    private $subCategoryTransactionRuleRepository;

    public function __construct(SubCategoryTransactionRuleRepository $subCategoryTransactionRuleRepository)
    {
        $this->subCategoryTransactionRuleRepository = $subCategoryTransactionRuleRepository;
    }

    public function getMatchingSubCategory(Transaction $transaction) : ?SubCategory
    {
        $rules = $this->subCategoryTransactionRuleRepository->findAll();
        $foundSubCategory = null;
        foreach ($rules as $rule) {
            $ruleType = $rule->getSubCategory()->getTransactionType();

            if (
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
