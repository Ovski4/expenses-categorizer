<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Repository\AccountRepository;

class TransactionFactory
{
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function createFromArray($array)
    {
        $transaction = new Transaction();

        $account = $array['accountId']
            ? $this->accountRepository->findOneById($array['accountId'])
            : $this->accountRepository->findByAliasOrName($array['account'])
        ;

        $transaction
            ->setAmount($array['value'])
            ->setCreatedAt(
                (\DateTime::createFromFormat('d/m/Y', $array['date']))->setTime(0, 0, 0)
            )
            ->setLabel($array['label'])
            ->setAccount($account)
        ;

        return $transaction;
    }
}
