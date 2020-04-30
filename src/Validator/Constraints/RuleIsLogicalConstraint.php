<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RuleIsLogicalConstraint extends Constraint
{
    public $message = 'rule_sub_category.is_logical';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return RuleIsLogicalConstraintValidator::class;
    }
}
