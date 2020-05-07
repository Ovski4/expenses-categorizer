<?php

namespace App\Form;

use App\Entity\Bank;
use App\Services\FileParser\AbstractFileParser;
use App\Services\FileParser\FileParserRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatementType extends AbstractType
{
    private $translator;

    private $fileParserRegistry;

    public function __construct(TranslatorInterface $translator, FileParserRegistry $fileParserRegistry)
    {
        $this->translator = $translator;
        $this->fileParserRegistry = $fileParserRegistry;
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
                'label' => 'File type',
                'required' => true
            ])
        ;
    }

    private function getChoices()
    {
        return array_reduce(
            $this->fileParserRegistry->getfileParsers(),
            function(array $choices, AbstractFileParser $fileParser) {
                $choices[$fileParser->getLabel()] = $fileParser->getName();

                return $choices;
            },
            []
        );
    }
}
