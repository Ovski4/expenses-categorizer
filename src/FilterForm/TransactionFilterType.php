<?php

namespace App\FilterForm;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class TransactionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS
            ])
            ->add('subCategory', Filters\BooleanFilterType::class, [
                'label' => 'Categorized',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if ($values['value'] === null) {
                        return null;
                    } else if ($values['value'] === 'y') {
                        $expression = $filterQuery->getExpr()->isNotNull($field);
                    } else if ($values['value'] === 'n') {
                        $expression = $filterQuery->getExpr()->isNull($field);
                    }

                    return $filterQuery->createCondition($expression);
                },
            ])
            ->add('amount', Filters\NumberRangeFilterType::class, [
                'left_number_options' => [
                    'label' => 'from_number',
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL
                ],
                'right_number_options' => [
                    'label' => 'to_number',
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL
                ]
            ])
            ->add('createdAt', Filters\DateRangeFilterType::class, [
                'left_date_options' => [
                    'widget' => 'single_text',
                    'label' => 'from_date'
                ],
                'right_date_options' => [
                    'widget' => 'single_text',
                    'label' => 'to_date'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'validation_groups' => ['filtering']
        ]);
    }
}