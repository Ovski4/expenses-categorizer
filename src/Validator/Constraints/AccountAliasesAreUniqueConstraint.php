<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AccountAliasesAreUniqueConstraint extends Constraint
{
    public $message = 'The alias "{{ alias }}" is already present for account "{{ account }}"';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return AccountAliasesAreUniqueConstraintValidator::class;
    }
}
