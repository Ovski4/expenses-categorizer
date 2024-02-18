<?php

namespace App\Form;

use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\TransactionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubCategoryType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach (TransactionType::getAll() as $transactionType) { 
            $choices[$transactionType] = $transactionType;
        }

        $builder
            ->add('name')
            ->add('topCategory', EntityType::class, [
                'class' => TopCategory::class,
                'group_by' => function($choice) {
                    return $this->translator->trans($choice->getTransactionType());
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubCategory::class,
        ]);
    }
}
