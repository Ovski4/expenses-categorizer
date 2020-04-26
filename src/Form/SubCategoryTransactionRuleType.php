<?php

namespace App\Form;

use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\SubCategoryTransactionRule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryTransactionRuleType extends AbstractCategoryRelatedType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (Operator::getAll() as $operator) {
            $choices[$this->translator->trans($operator)] = $operator;
        }

        $entity = $builder->getData();
        $builder
            ->add('contains')
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choices' => $this->getChoices($entity->getTransactionType()),
            ])
            ->add(
                'amount', NumberType::class, [
                'required' => false
            ])
            ->add('operator', ChoiceType::class, [
                'choices' => $choices,
                'required' => false,
                'data' => null
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
