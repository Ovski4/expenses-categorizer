<?php

namespace App\Form;

use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use App\Entity\TransactionType as EntityTransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractCategoryRelatedType extends AbstractType
{
    protected $entityManager;

    protected $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    protected function getChoices($transactionType = null, $class = SubCategory::class)
    {
        $choices = [$this->translator->trans('No sub category selected') => null];

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

            $choices[$this->translator->trans(EntityTransactionType::EXPENSES)] = $expensesSubCategories;
            $choices[$this->translator->trans(EntityTransactionType::REVENUES)] = $revenuesSubCategories;
        }

        return $choices;
    }
}
