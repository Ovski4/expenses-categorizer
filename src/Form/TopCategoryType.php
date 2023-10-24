<?php

namespace App\Form;

use App\Entity\TopCategory;
use App\Entity\TransactionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TopCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach (TransactionType::getAll() as $transactionType) { 
            $choices[$transactionType] = $transactionType;
        }

        $builder
            ->add('name')
            ->add('transactionType', ChoiceType::class, [
                'choices'  => $choices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TopCategory::class,
        ]);
    }
}
