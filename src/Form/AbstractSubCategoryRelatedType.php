<?php

namespace App\Form;

use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use App\Entity\TransactionType as EntityTransactionType;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractSubCategoryRelatedType extends AbstractType
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function getChoices()
    {
        $expensesSubCategories = $this->entityManager
            ->getRepository(SubCategory::class)
            ->findBy(
                ['transaction_type' => EntityTransactionType::EXPENSES],
                ['name' => 'asc']
            )
        ;
        $revenuesSubCategories = $this->entityManager
            ->getRepository(SubCategory::class)
            ->findBy(
                ['transaction_type' => EntityTransactionType::REVENUES],
                ['name' => 'asc']
            )
        ;

        return[
            'No sub category defined' => null,
            EntityTransactionType::EXPENSES => $this->formatChoices($expensesSubCategories),
            EntityTransactionType::REVENUES => $this->formatChoices($revenuesSubCategories),
        ];
    }

    private function formatChoices($subCategories)
    {
        $choices = [];
        foreach ($subCategories as $subCategory) { 
            $choices[$subCategory->getName()] = $subCategory->getId();
        }

        return $choices;
    }
}
