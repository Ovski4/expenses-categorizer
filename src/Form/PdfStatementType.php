<?php

namespace App\Form;

use App\Entity\Settings;
use App\Services\FileParser\AbstractFileParser;
use App\Services\FileParser\FileParserRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class PdfStatementType extends AbstractType
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
                'label' => 'PDF file',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('Please upload a valid PDF document'),
                    ])
                ],
            ])
            ->add('parserName', ChoiceType::class, [
                'choices'  => $this->getChoices(),
                'preferred_choices' => $this->getPreferredChoices(),
                'label' => 'File type',
                'required' => true
            ])
        ;
    }

    private function getPreferredChoices()
    {
        $lastParserUsedSettings = $this->entityManager
            ->getRepository(Settings::class)
            ->findOneByName(Settings::NAME_LAST_PDF_STATEMENT_PARSER_USED)
        ;

        $choices = $this->getChoices();

        if (!is_null($lastParserUsedSettings)) {
            $parserName = $lastParserUsedSettings->getValue();
            $parserLabel = array_search($parserName, $choices);

            return [ $parserLabel => $parserName ];
        }

        return [];
    }

    private function getChoices()
    {
        return array_reduce(
            $this->fileParserRegistry->getFileParsers( AbstractFileParser::FILE_TYPE_PDF ),
            function(array $choices, AbstractFileParser $fileParser) {
                $choices[$fileParser->getLabel()] = $fileParser->getName();

                return $choices;
            },
            []
        );
    }
}
