<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class RuleIsLogicalConstraint extends Constraint
{
    public $message = 'rule_sub_category.is_logical';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return RuleIsLogicalConstraintValidator::class;
    }
}
