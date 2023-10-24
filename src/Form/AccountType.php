<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('aliases', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'attr' => ['class' => 'collection'],
            ])
            ->add('currency', CurrencyType::class, [
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
