<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AccountAliasesAreUniqueConstraint extends Constraint
{
    public $message = 'account.alias_already_present';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return AccountAliasesAreUniqueConstraintValidator::class;
    }
}
