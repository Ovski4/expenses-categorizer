<?php

namespace App\FilterForm;

use App\Entity\Account;
use App\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Spiriit\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionFilterType extends AbstractType
{
    use SubCategoryFilterTypeTrait;

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'placeholder' => 'Biocoop...'
                ]
            ])
            ->add('account', Filters\EntityFilterType ::class, [
                'class' => Account::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.name', 'asc');
                },
            ])
            ->add('subCategory', Filters\EntityFilterType ::class,
                $this->getSubCategoryFilterTypeOptions(),
            )
            ->add('tag', Filters\EntityFilterType::class, [
                'class' => Tag::class,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $tag = $values['value'];
                    if ($tag === null) {
                        return null;
                    }

                    $filterQuery->getQueryBuilder()->innerJoin('transaction.tags', 'tags');
                    $expression = $filterQuery->getExpr()->in('tags.id', ':tags');
                    $parameters = ['tags' => $tag->getId()];

                    return $filterQuery->createCondition($expression, $parameters);
                },
            ])
            ->add('categorized', Filters\BooleanFilterType::class, [
                'property_path' => '[subCategory]',
                'label' => 'Categorized',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $categorized = $values['value'];
                    if ($categorized === null) {
                        return null;
                    }

                    $field = sprintf('%s.subCategory', $values['alias']);

                    if ($categorized === 'y') {
                        $expression = $filterQuery->getExpr()->isNotNull($field);
                    } else if ($categorized === 'n') {
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
                'label' => 'Created',
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

    public function getBlockPrefix(): string
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection'   => false,
            'validation_groups' => ['filtering']
        ]);
    }
}