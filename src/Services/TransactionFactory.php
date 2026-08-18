<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Repository\AccountRepository;

class TransactionFactory
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param array<string, mixed> $array
     */
    public function createFromArray($array): Transaction
    {
        $transaction = new Transaction();

        $account = isset($array['accountId'])
            ? $this->accountRepository->find($array['accountId'])
            : $this->accountRepository->findByAliasOrName($array['account'])
        ;

        $createdAt = \DateTime::createFromFormat('d/m/Y', $array['date']);

        if (false === $createdAt) {
            throw new \InvalidArgumentException(sprintf('The transaction date "%s" does not match the expected d/m/Y format.', $array['date']));
        }

        $transaction
            ->setAmount($array['value'])
            ->setCreatedAt($createdAt->setTime(0, 0, 0))
            ->setLabel($array['label'])
            ->setAccount($account)
        ;

        return $transaction;
    }
}
