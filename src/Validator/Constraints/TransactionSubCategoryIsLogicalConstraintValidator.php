<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TransactionSubCategoryIsLogicalConstraintValidator extends ConstraintValidator
{
    public function validate($transaction, Constraint $constraint)
    {
        if (null === $transaction || '' === $transaction) {
            return;
        }

        try {
            $transaction->checkSubCategory();
        } catch (\Exception $e) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ sign }}', $transaction->getAmount() > 0 ? 'positive' : 'negative')
                ->setParameter('{{ transaction_type }}', strtolower($transaction->getSubCategory()->getTransactionType()))
                ->addViolation()
            ;
        }
    }
}
