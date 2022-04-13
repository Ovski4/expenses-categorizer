<?php

namespace App\Services;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class TransactionDiffChecker
{
    private ?EntityManagerInterface $entityManager = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function subCategoryChanged(Transaction $transaction): bool
    {
        $originalTransactionData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($transaction);

        if (is_null($originalTransactionData['subCategory']) && is_null($transaction->getSubCategory())) {
            return false;
        }

        if (is_null($originalTransactionData['subCategory']) || is_null($transaction->getSubCategory())) {
            return true;
        }

        return $originalTransactionData['subCategory']->getId() !== $transaction->getSubCategory()->getId();
    }
}
