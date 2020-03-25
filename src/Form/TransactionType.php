<?php

namespace App\Form;

use App\Entity\SubCategory;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractCategoryRelatedType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label')
            ->add('amount')
            ->add('created_at')
            ->add('account')
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choices' => $this->getChoices(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
