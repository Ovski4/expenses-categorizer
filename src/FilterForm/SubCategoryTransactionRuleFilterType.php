<?php

namespace App\FilterForm;

use Spiriit\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Spiriit\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubCategoryTransactionRuleFilterType extends AbstractType
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
            ->add('contains', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS
            ])
            ->add('subCategory', Filters\EntityFilterType ::class,
                $this->getSubCategoryFilterTypeOptions()
            )
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