<?php

namespace App\Validator\Constraints;

use App\Repository\AccountRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AccountAliasesAreUniqueConstraintValidator extends ConstraintValidator
{
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function validate($account, Constraint $constraint): void
    {
        if (!$constraint instanceof AccountAliasesAreUniqueConstraint) {
            throw new UnexpectedTypeException($constraint, AccountAliasesAreUniqueConstraint::class);
        }

        if (null === $account || '' === $account) {
            return;
        }

        foreach ($account->getAliases() as $alias) {
            $otherAccount = $this->accountRepository->findWithAliasExceptAccount($alias, $account->getId());
            if (null !== $otherAccount) {
                // in case of an alias which is a substring of another
                if (in_array($alias, $otherAccount->getAliases())) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->setParameter('%alias%', $alias)
                        ->setParameter('%account%', $otherAccount)
                        ->addViolation()
                    ;
                }
            }
        }
    }
}
