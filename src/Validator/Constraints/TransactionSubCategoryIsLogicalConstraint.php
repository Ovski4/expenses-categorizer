<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TransactionSubCategoryIsLogicalConstraint extends Constraint
{
    public $message = 'transaction_sub_category.is_logical';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return TransactionSubCategoryIsLogicalConstraintValidator::class;
    }
}
