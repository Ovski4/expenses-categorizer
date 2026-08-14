<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RuleIsCompleteConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $rule, Constraint $constraint): void
    {
        if (!$constraint instanceof RuleIsCompleteConstraint) {
            throw new UnexpectedTypeException($constraint, RuleIsCompleteConstraint::class);
        }

        if (null === $rule || '' === $rule) {
            return;
        }

        try {
            $rule->checkOperatorAndAmountFields();
        } catch (\Exception $e) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
