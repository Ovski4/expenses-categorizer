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
        $transaction
            ->setAmount($array['value'])
            ->setCreatedAt(\DateTime::createFromFormat('d/m/Y', $array['date']))
            ->setLabel($array['label'])
            ->setAccount($this->accountRepository->findByAliasOrName($array['account']))
        ;

        return $transaction;
    }
}