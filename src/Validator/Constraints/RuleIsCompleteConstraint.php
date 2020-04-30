<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RuleIsCompleteConstraint extends Constraint
{
    public $message = 'rule.is_complete';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return RuleIsCompleteConstraintValidator::class;
    }
}
