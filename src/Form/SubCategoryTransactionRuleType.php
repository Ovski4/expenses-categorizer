<?php

namespace App\Form;

use App\Entity\SubCategory;
use App\Entity\SubCategoryTransactionRule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryTransactionRuleType extends AbstractCategoryRelatedType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $builder
            ->add('contains')
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choices' => $this->getChoices($entity->getTransactionType()),
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
