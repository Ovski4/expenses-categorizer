<?php

namespace App\Form;

use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\TransactionType;
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
            $operatorChoices[$this->translator->trans($operator)] = $operator;
        }

        foreach (TransactionType::getAll() as $transactionType) {
            $transactionTypeChoices[$this->translator->trans($transactionType)] = $transactionType;
        }

        $builder
            ->add('transactionType', ChoiceType::class, [
                'choices' => $transactionTypeChoices,
                'required' => true
            ])
            ->add('contains')
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choices' => $this->getChoices(),
            ])
            ->add(
                'amount', NumberType::class, [
                'required' => false
            ])
            ->add('operator', ChoiceType::class, [
                'help' => 'Select which operator to use to compare transactions amount with this rule amount',
                'choices' => $operatorChoices,
                'required' => false
            ])
            ->add('priority', NumberType::class, [
                'help' => 'If a transaction matches multiple rules, the rule with the highest priority will prevail',
                'html5' => true,
                'required' => true,
                'empty_data' => 0
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
