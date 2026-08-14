<?php

namespace App\Validator\Constraints;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\TransactionType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuleIsLogicalConstraintValidator extends ConstraintValidator
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate(mixed $rule, Constraint $constraint): void
    {
        if (!$constraint instanceof RuleIsLogicalConstraint) {
            throw new UnexpectedTypeException($constraint, RuleIsLogicalConstraint::class);
        }

        if (null === $rule
            || !$rule instanceof SubCategoryTransactionRule
            || null === $rule->getAmount()
        ) {
            return;
        }

        if (
            (TransactionType::EXPENSES === $rule->getTransactionType() && $rule->getAmount() > 0)
            || (TransactionType::REVENUES === $rule->getTransactionType() && $rule->getAmount() < 0)
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
