<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TransactionSubCategoryIsLogicalConstraint extends Constraint
{
    public $message = 'transaction_sub_category.is_logical';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return TransactionSubCategoryIsLogicalConstraintValidator::class;
    }
}
