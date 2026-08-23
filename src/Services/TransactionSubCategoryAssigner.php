<?php

namespace App\Services;

use App\Entity\SubCategory;
use App\Entity\Transaction;
use App\Exception\InvalidSubCategoryAssignmentException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Assigns a sub category to a transaction.
 *
 * The sub category is not nullable on purpose: this service can only set a
 * category, never clear one.
 */
class TransactionSubCategoryAssigner
{
    private EntityManagerInterface $entityManager;
    private TransactionDiffChecker $transactionDiffChecker;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        TransactionDiffChecker $transactionDiffChecker,
        ValidatorInterface $validator,
    ) {
        $this->entityManager = $entityManager;
        $this->transactionDiffChecker = $transactionDiffChecker;
        $this->validator = $validator;
    }

    /**
     * @throws InvalidSubCategoryAssignmentException when the sub category does not
     *                                               match the transaction type
     */
    public function assign(Transaction $transaction, SubCategory $subCategory): void
    {
        $transaction->setSubCategory($subCategory);

        if ($this->transactionDiffChecker->subCategoryChanged($transaction)) {
            $transaction->setCategorizedManually(true);
        }

        // Transaction::checkSubCategory() throws in a PreUpdate callback when the
        // types do not match, which would surface as a 500. Validate before flushing.
        $violations = $this->validator->validate($transaction);

        if (count($violations) > 0) {
            throw new InvalidSubCategoryAssignmentException((string) $violations->get(0)->getMessage());
        }

        $this->entityManager->flush();
    }
}
