<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Annotation
 */
class AccountAliasesAreUniqueConstraint extends Constraint
{
    public $message = 'account.alias_already_present';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return AccountAliasesAreUniqueConstraintValidator::class;
    }
}
