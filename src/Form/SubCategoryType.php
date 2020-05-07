<?php

namespace App\Form;

use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\TransactionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryType extends AbstractCategoryRelatedType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach (TransactionType::getAll() as $transactionType) { 
            $choices[$transactionType] = $transactionType;
        }

        $builder
            ->add('name')
            ->add('topCategory', EntityType::class, [
                'class' => TopCategory::class,
                'choices' => $this->getChoices(TopCategory::class),
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
