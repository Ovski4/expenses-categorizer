<?php

namespace App\Form;

use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\TransactionType;
use App\Exception\IllogicalRuleException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryTransactionRuleType extends AbstractCategoryRelatedType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (Operator::getAll() as $operator) {
            $operatorChoices[$this->translator->trans($operator)] = $operator;
        }

        foreach (TransactionType::getAll() as $transactionType) {
            $transactionTypeChoices[$this->translator->trans($transactionType)] = $transactionType;
        }

        $transactionTypeOptions = [
            'choices' => $transactionTypeChoices,
            'required' => true
        ];

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
            ->add('transactionType', ChoiceType::class, $transactionTypeOptions)
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
            ->setDataMapper($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubCategoryTransactionRule::class,
        ]);
    }

    private function getReversedOperator($transactionType, $operator): ?string
    {
        if ($transactionType == TransactionType::EXPENSES) {

            if ($operator == Operator::GREATER_THAN_OR_EQUAL) {
                return Operator::LOWER_THAN_OR_EQUAL;
            }

            if ($operator == Operator::LOWER_THAN_OR_EQUAL) {
                return Operator::GREATER_THAN_OR_EQUAL;
            }
        }

        return $operator;
    }

    public function mapDataToForms($viewData, $forms)
    {
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof SubCategoryTransactionRule) {
            throw new UnexpectedTypeException($viewData, SubCategoryTransactionRule::class);
        }

        $forms = iterator_to_array($forms);
        $forms['contains']->setData($viewData->getContains());
        $forms['subCategory']->setData($viewData->getSubCategory());
        $forms['amount']->setData($viewData->getDisplayableAmount());
        $forms['transactionType']->setData($viewData->getTransactionType());
        $forms['operator']->setData($viewData->getReversedOperator());
        $forms['priority']->setData($viewData->getPriority());
    }

    public function mapFormsToData($forms, &$viewData)
    {
        $forms = iterator_to_array($forms);

        $transactionType = $forms['transactionType']->getData();
        $subCategory = $forms['subCategory']->getData();

        if ($subCategory->getTransactionType() !== $transactionType) {
            throw new IllogicalRuleException($transactionType, $subCategory);
        }

        if ( $forms['amount']->getData() < 0) {
            throw new \UnexpectedValueException();
        }

        $amount = $transactionType == TransactionType::EXPENSES ?
            -1 * $forms['amount']->getData() :
            $forms['amount']->getData()
        ;

        $viewData
            ->setContains($forms['contains']->getData())
            ->setAmount($amount)
            ->setOperator($this->getReversedOperator($transactionType, $forms['operator']->getData()))
            ->setPriority($forms['priority']->getData())
            ->setSubCategory($subCategory)
        ;
    }
}
