<?php

namespace App\Event;

use App\Entity\SubCategory;
use App\Entity\Transaction;
use Symfony\Contracts\EventDispatcher\Event;

class TransactionCategoryChangedEvent extends Event
{
    public const NAME = 'transaction.category_changed';

    private SubCategory $oldSubCategory;

    private Transaction $transaction;

    public function __construct(Transaction $transaction, SubCategory $oldSubCategory)
    {
        $this->transaction = $transaction;
        $this->oldSubCategory = $oldSubCategory;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function getOldSubCategory(): SubCategory
    {
        return $this->oldSubCategory;
    }
}
