<?php

namespace App\Validator\Constraints;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\TransactionType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuleIsLogicalConstraintValidator extends ConstraintValidator
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($rule, Constraint $constraint): void
    {
        if (null === $rule
            || '' === $rule
            || !$rule instanceof SubCategoryTransactionRule
            || null === $rule->getAmount()
        ) {
            return;
        }

        if (
            ($rule->getTransactionType() === TransactionType::EXPENSES && $rule->getAmount() > 0)
            || ($rule->getTransactionType() === TransactionType::REVENUES && $rule->getAmount() < 0)
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%sign%', $this->translator->trans($rule->getAmount() > 0 ? 'positive' : 'negative'))
                ->setParameter('%transaction_type%', strtolower($this->translator->trans($rule->getTransactionType())))
                ->addViolation()
            ;
        }
    }
}
