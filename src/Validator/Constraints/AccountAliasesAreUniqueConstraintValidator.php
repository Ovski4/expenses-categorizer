<?php

namespace App\Validator\Constraints;

use App\Repository\AccountRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AccountAliasesAreUniqueConstraintValidator extends ConstraintValidator
{
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function validate($account, Constraint $constraint): void
    {
        if (null === $account || '' === $account) {
            return;
        }

        foreach($account->getAliases() as $alias) {
            $otherAccount = $this->accountRepository->findWithAliasExceptAccount($alias, $account->getId());
            if ($otherAccount !== null) {
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
