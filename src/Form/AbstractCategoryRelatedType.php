<?php

namespace App\Form;

use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use App\Entity\TransactionType as EntityTransactionType;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractCategoryRelatedType extends AbstractType
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function getChoices($transactionType = null, $class = SubCategory::class)
    {
        $choices = ['No sub category defined' => null];

        if ($transactionType != null) {
            $subCategories = $this->entityManager
                ->getRepository($class)
                ->findByTransactionType($transactionType)
            ;

            $choices[$transactionType] = $subCategories;
        } else {
            $expensesSubCategories = $this->entityManager
                ->getRepository($class)
                ->findByTransactionType(EntityTransactionType::EXPENSES)
            ;
            $revenuesSubCategories = $this->entityManager
                ->getRepository($class)
                ->findByTransactionType(EntityTransactionType::REVENUES)
            ;

            $choices[EntityTransactionType::EXPENSES] = $expensesSubCategories;
            $choices[EntityTransactionType::REVENUES] = $revenuesSubCategories;
        }

        return $choices;
    }
}
