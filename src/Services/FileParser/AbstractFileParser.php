<?php

namespace App\Services\FileParser;

use App\Services\TransactionFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFileParser
{
    const FILE_TYPE_PDF = 'pdf';
    const FILE_TYPE_CSV = 'csv';

    protected $transactionFactory;

    protected $params;

    protected $resolver;

    public function __construct(TransactionFactory $transactionFactory, ParameterBagInterface $params)
    {
        $this->transactionFactory = $transactionFactory;
        $this->params = $params;
        $this->resolver = ( new OptionsResolver() )
            ->setDefaults(['accountId' => null])
            ->setAllowedTypes('accountId', ['string', 'null']);
    }

    abstract public function getName(): string;
    abstract public function getFileType(): string;
    abstract public function getLabel(): string;
    abstract public function parse(string $filePath, array $options): array;
}
