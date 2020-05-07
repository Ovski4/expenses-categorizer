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
        $matchingRules = [];

        foreach ($this->rules as $rule) {
            if ($this->ruleMatchesTransaction($rule, $transaction)) {
                $matchingRules[] = $rule;
            }
        }

        $bestRule = $this->getBestRule($transaction, $matchingRules);

        return $bestRule === null ? null : $bestRule->getSubCategory();
    }

    private function ruleMatchesTransaction($rule, $transaction)
    {
        // label is not within rule "contains" property
        if (strpos($transaction->getLabel(), $rule->getContains()) === false) {
            return false;
        }

        // types differ
        if ($transaction->getType() !== $rule->getSubCategory()->getTransactionType()) {
            return false;
        }

        $ruleAmount = $rule->getAmount();
        $ruleOperator = $rule->getOperator();
        $transactionAmount = abs($transaction->getAmount());

        if ($ruleAmount !== null && $ruleOperator !== null) {

            if ($ruleOperator == Operator::EQUALS && $transactionAmount !== $ruleAmount) {
                return false;
            }

            if ($ruleOperator == Operator::GREATER_THAN_OR_EQUAL && $transactionAmount < $ruleAmount) {
                return false;
            }

            if ($ruleOperator == Operator::LOWER_THAN_OR_EQUAL && $transactionAmount > $ruleAmount) {
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

    private function filterRulesWithHighestPriorities(array $rules)
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

    private function getHighestPriorityValue(array $rules)
    {
        $highestPriority = null;

        foreach ($rules as $rule) {
            if ($highestPriority === null) {
                $highestPriority = $rule->getPriority();
            } else if ($rule->getPriority() > $highestPriority) {
                $highestPriority = $rule->getPriority();
            }
        }

        return $highestPriority;
    }

    private function allRulesHaveTheSameSubCategory(array $rules)
    {
        $subCategory = null;

        foreach ($rules as $rule) {
            if ($subCategory === null) {
                $subCategory = $rule->getSubCategory();
            } else if ($subCategory != $rule->getSubCategory()) {
                return false;
            }
        }

        return true;
    }
}
