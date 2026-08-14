<?php

namespace App\Services;

use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\Transaction;
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

    public function setRules(): void
    {
        $this->rules = $this->repository->findAll()
        ;
    }

    public function getMatchingSubCategory(Transaction $transaction): ?SubCategory
    {
        $matchingRules = [];

        foreach ($this->rules as $rule) {
            if ($this->ruleMatchesTransaction($rule, $transaction)) {
                $matchingRules[] = $rule;
            }
        }

        $bestRule = $this->getBestRule($transaction, $matchingRules);

        return null === $bestRule ? null : $bestRule->getSubCategory();
    }

    private function ruleMatchesTransaction($rule, $transaction): bool
    {
        // label is not within rule "contains" property (case-insensitive check)
        $lowerCaseTransactionLabel = strtolower($transaction->getLabel());
        $lowerCaseRuleContains = strtolower($rule->getContains());
        if (false === strpos($lowerCaseTransactionLabel, $lowerCaseRuleContains)) {
            return false;
        }

        // types differ
        if ($transaction->getType() !== $rule->getTransactionType()) {
            return false;
        }

        $ruleOperator = $rule->getOperator();
        $ruleAmount = $rule->getAmount();
        $transactionAmount = $transaction->getAmount();

        if (null !== $ruleAmount && null !== $ruleOperator) {
            if (Operator::EQUALS == $ruleOperator && $transactionAmount !== $ruleAmount) {
                return false;
            }

            if (Operator::GREATER_THAN_OR_EQUAL == $ruleOperator && $transactionAmount < $ruleAmount) {
                return false;
            }

            if (Operator::LOWER_THAN_OR_EQUAL == $ruleOperator && $transactionAmount > $ruleAmount) {
                return false;
            }
        }

        return true;
    }

    private function getBestRule(Transaction $transaction, array $rules): ?SubCategoryTransactionRule
    {
        $rules = $this->filterRulesWithHighestPriorities($rules);

        if (!$this->allRulesHaveTheSameSubCategory($rules)) {
            throw new TransactionMatchesMultipleRulesException($transaction, $rules);
        }

        return $rules[0] ?? null;
    }

    /**
     * @return SubCategoryTransactionRule[]
     */
    private function filterRulesWithHighestPriorities(array $rules): array
    {
        $filteredRules = [];
        $highestPriority = $this->getHighestPriorityValue($rules);

        foreach ($rules as $rule) {
            if ($rule->getPriority() === $highestPriority) {
                $filteredRules[] = $rule;
            }
        }

        return $filteredRules;
    }

    private function getHighestPriorityValue(array $rules): ?int
    {
        $highestPriority = null;

        foreach ($rules as $rule) {
            if (null === $highestPriority) {
                $highestPriority = $rule->getPriority();
            } elseif ($rule->getPriority() > $highestPriority) {
                $highestPriority = $rule->getPriority();
            }
        }

        return $highestPriority;
    }

    private function allRulesHaveTheSameSubCategory(array $rules): bool
    {
        $subCategory = null;

        foreach ($rules as $rule) {
            if (null === $subCategory) {
                $subCategory = $rule->getSubCategory();
            } elseif ($subCategory != $rule->getSubCategory()) {
                return false;
            }
        }

        return true;
    }
}
