<?php

namespace App\Form;

use App\Entity\SubCategory;
use App\Entity\Transaction;
use App\Entity\TransactionType as EntityTransactionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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

        $builder
            ->add('label')
            ->add('amount')
            ->add('created_at')
            ->add('account')
            ->add('sub_category', ChoiceType::class, [
                'choices' => [
                    'No sub category defined' => null,
                    EntityTransactionType::EXPENSES => $this->getChoices($expensesSubCategories),
                    EntityTransactionType::REVENUES => $this->getChoices($revenuesSubCategories),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }

    private function getChoices($subCategories) {
        $choices = [];
        foreach ($subCategories as $subCategory) { 
            $choices[$subCategory->getName()] = $subCategory->getId();
        }

        return $choices;
    }
}
