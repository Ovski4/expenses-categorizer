<?php

namespace App\Services;

use App\Entity\SubCategory;
use App\Entity\Transaction;
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
        foreach ($rules as $rule) {
            if (strpos($transaction->getLabel(), $rule->getContains()) !== false) {
                return $rule->getSubCategory();
            }
        }

        return null;
    }
}
