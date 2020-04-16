<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TransactionSubCategoryIsLogicalConstraint extends Constraint
{
    public $message = 'The sign of the transaction amount ({{ sign }}) and the selected sub category (in {{ transaction_type }}) does not make sense';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return TransactionSubCategoryIsLogicalConstraintValidator::class;
    }
}
