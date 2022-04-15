<?php

namespace App\Form;

use App\Entity\Account;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvStatementType extends AbstractType
{
    private $translator;
    private $requestStack;
    private $slugger;

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, SluggerInterface $slugger)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->slugger = $slugger;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('statement', FileType::class, [
                'label' => 'CSV file',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('Please upload a valid CSV document'),
                    ])
                ],
            ])
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'required' => true,
                // prefer accounts whose names are closer to the parser name.
                'preferred_choices' => function ($account, $key, $value) {
                    $slugifiedAccountName = $this->slugger->slug($account->getName())->lower()->toString();
                    $request = $this->requestStack->getCurrentRequest();
                    $parserName = $request->attributes->get('parserName');
                    $checkedValues = [
                        'credit' => ['credit'],
                        'check' => ['check', 'cheque']
                    ];

                    foreach($checkedValues as $parserPart => $accountNameParts) {
                        foreach($accountNameParts as $accountNamePart) {
                            if (strpos($slugifiedAccountName, $accountNamePart) !== false
                                && strpos($parserName, $parserPart) !== false
                            ) {
                                return true;
                            }
                        }
                    }

                    return false;
                },
                // Accounts with aliases don't need the account to be selected
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('a');

                    return $qb->where($qb->expr()->isNull('a.aliases'));
                },
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }
}
