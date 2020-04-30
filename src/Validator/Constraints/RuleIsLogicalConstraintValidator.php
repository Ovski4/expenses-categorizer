<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuleIsLogicalConstraintValidator extends ConstraintValidator
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($rule, Constraint $constraint)
    {
        if (null === $rule || '' === $rule) {
            return;
        }

        try {
            $rule->checkSubCategory();
        } catch (\Exception $e) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%sign%', $this->translator->trans($rule->getAmount() > 0 ? 'positive' : 'negative'))
                ->setParameter('%transaction_type%', strtolower($this->translator->trans($rule->getSubCategory()->getTransactionType())))
                ->addViolation()
            ;
        }
    }
}
