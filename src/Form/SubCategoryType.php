<?php

namespace App\Form;

use App\Entity\SubCategory;
use App\Entity\TransactionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SubCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach (TransactionType::getAll() as $transactionType) { 
            $choices[$transactionType] = $transactionType;
        }

        $builder
            ->add('name')
            ->add('top_category')
            ->add('transaction_type', ChoiceType::class, [
                'choices'  => $choices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubCategory::class,
        ]);
    }
}
