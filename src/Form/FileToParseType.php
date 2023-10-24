<?php

namespace App\Form;

use App\Entity\Account;
use App\Services\FileParser\AbstractFileParser;
use App\Services\FileParser\AccountGuessable;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileToParseType extends AbstractType
{
    private $translator;
    private $locale;

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $parser = $options['fileParser'];
        $fileType = $parser->getFileType();

        $builder
            ->add('statement', FileType::class, [
                'label' => strtoupper($fileType) . ' file',
                'attr' => [
                    'lang' => $this->locale,
                ],
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => $parser->getAllowedMimeTypes(),
                        'mimeTypesMessage' => $this->translator->trans(sprintf(
                            'Please upload a valid %s document',
                            strtoupper($fileType)
                        )),
                    ]),
                ],
            ])
        ;

        if (!$parser->extractsAccountsFromFile()) {
            $builder
                ->add('account', EntityType::class, [
                    'class' => Account::class,
                    'required' => true,
                    // prefer accounts that matches the parser.
                    'preferred_choices' => function ($account) use ($parser) {
                        if ($parser instanceof AccountGuessable) {
                            return $parser->matches($account);
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('fileParser')
            ->setAllowedTypes('fileParser', AbstractFileParser::class)
        ;
    }
}
