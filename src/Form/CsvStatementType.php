<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Settings;
use App\Services\FileParser\AbstractFileParser;
use App\Services\FileParser\FileParserRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvStatementType extends AbstractType
{
    private $translator;

    private $fileParserRegistry;

    private $entityManager;

    public function __construct(
        TranslatorInterface $translator,
        FileParserRegistry $fileParserRegistry,
        EntityManagerInterface $entityManager
    ) {
        $this->translator = $translator;
        $this->fileParserRegistry = $fileParserRegistry;
        $this->entityManager = $entityManager;
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
                'required' => true
            ])
            ->add('parserName', ChoiceType::class, [
                'choices'  => $this->getParserChoices(),
                'preferred_choices' => $this->getParserPreferredChoices(),
                'label' => 'File type',
                'required' => true
            ])
        ;
    }

    private function getParserPreferredChoices()
    {
        $lastParserUsedSettings = $this->entityManager
            ->getRepository(Settings::class)
            ->findOneByName(Settings::NAME_LAST_CSV_PARSER_USED)
        ;

        $choices = $this->getParserChoices();

        if (!is_null($lastParserUsedSettings)) {
            $parserName = $lastParserUsedSettings->getValue();
            $parserLabel = array_search($parserName, $choices);

            return [ $parserLabel => $parserName ];
        }

        return [];
    }

    private function getParserChoices()
    {
        return array_reduce(
            $this->fileParserRegistry->getFileParsers( AbstractFileParser::FILE_TYPE_CSV ),
            function(array $choices, AbstractFileParser $fileParser) {
                $choices[$fileParser->getLabel()] = $fileParser->getName();

                return $choices;
            },
            []
        );
    }
}
