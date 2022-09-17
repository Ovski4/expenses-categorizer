<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class RuleIsCompleteConstraint extends Constraint
{
    public $message = 'rule.is_complete';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return RuleIsCompleteConstraintValidator::class;
    }
}
