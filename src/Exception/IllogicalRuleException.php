<?php

namespace App\Exception;

use App\Entity\SubCategory;

class IllogicalRuleException extends \Exception
{
    private $transactionType;

    private $subCategory;

    public function __construct(string $transactionType, SubCategory $subCategory)
    {
        $this->transactionType = $transactionType;
        $this->subCategory = $subCategory;

        parent::__construct(sprintf(
            'Transaction type is set to "%s" but the sub category belongs to "%s"',
            $transactionType,
            $subCategory->getTransactionType()
        ));
    }

    public function getTransactionType()
    {
        return $this->transactionType;
    }

    public function getSubCategory()
    {
        return $this->subCategory;
    }
}
