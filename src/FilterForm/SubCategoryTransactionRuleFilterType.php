<?php

namespace App\FilterForm;

use App\Entity\SubCategory;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class SubCategoryTransactionRuleFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contains', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS
            ])
            ->add('subCategory', Filters\EntityFilterType ::class, [
                'class' => SubCategory::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'validation_groups' => array('filtering')
        ]);
    }
}