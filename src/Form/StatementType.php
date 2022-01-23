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

class StatementType extends AbstractType
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
                'label' => 'File type',
                'required' => true
            ])
        ;
    }

    private function getChoices()
    {
        $choices = array_reduce(
            $this->fileParserRegistry->getfileParsers(),
            function(array $choices, AbstractFileParser $fileParser) {
                $choices[$fileParser->getLabel()] = $fileParser->getName();

                return $choices;
            },
            []
        );

        $lastParserUsedSettings = $this->entityManager
            ->getRepository(Settings::class)
            ->findOneByName(Settings::NAME_LAST_PARSER_USED)
        ;

        if (!is_null($lastParserUsedSettings)) {
            return $this->moveToFirstPosition($choices, $lastParserUsedSettings->getValue());
        }

        return $choices;
    }

    /**
     * Find the item in array with the given value and mov it at first position
     */
    private function moveToFirstPosition(array $associativeArray, string $firstItemValue): array
    {
        $reArrangedArray = [];

        foreach ($associativeArray as $key => $value) {
            if ($value === $firstItemValue) {
                $reArrangedArray[$key] = $value;
            }
        }

        foreach ($associativeArray as $key => $value) {
            if ($value !== $firstItemValue) {
                $reArrangedArray[$key] = $value;
            }
        }

        return $reArrangedArray;
    }
}
