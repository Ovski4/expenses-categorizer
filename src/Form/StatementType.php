<?php

namespace App\Form;

use App\Entity\Bank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatementType extends AbstractType
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('statement', FileType::class, [
                'label' => 'Statement (PDF file)',
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
                'label' => 'Bank',
                'required' => true
            ])
        ;
    }

    private function getChoices()
    {
        return array_reduce(Bank::getAll(),function($carry, $item) {
            $carry[$item['name']] = $item['parserName'];

            return $carry;
        }, []);
    }
}
