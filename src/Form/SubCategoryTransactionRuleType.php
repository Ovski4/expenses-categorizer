<?php

namespace App\Form;

use App\Entity\SubCategoryTransactionRule;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryTransactionRuleType extends AbstractSubCategoryRelatedType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contains')
            ->add('sub_category', ChoiceType::class, [
                'choices' => $this->getChoices()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubCategoryTransactionRule::class,
        ]);
    }
}
