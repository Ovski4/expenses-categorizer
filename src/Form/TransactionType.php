<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\SubCategory;
use App\Entity\Tag;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('amount')
            ->add('createdAt', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'required' => true
            ])
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'group_by' => function($choice) {
                    return $this->translator->trans($choice->getTransactionType());
                },
                'required' => false,
            ])
            ->add('tags', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'label' => false,
                    'class' => Tag::class,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'attr' => [
                    'class' => 'collection'
                ],
                'by_reference' => false,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
