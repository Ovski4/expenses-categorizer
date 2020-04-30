<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RuleIsCompleteConstraintValidator extends ConstraintValidator
{
    public function validate($rule, Constraint $constraint)
    {
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
