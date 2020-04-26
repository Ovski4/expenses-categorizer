<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionSubCategoryIsLogicalConstraintValidator extends ConstraintValidator
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
                ->setParameter('%sign%', $this->translator->trans($transaction->getAmount() > 0 ? 'positive' : 'negative'))
                ->setParameter('%transaction_type%', strtolower($this->translator->trans($transaction->getSubCategory()->getTransactionType())))
                ->addViolation()
            ;
        }
    }
}
