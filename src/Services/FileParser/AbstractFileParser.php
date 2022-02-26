<?php

namespace App\Services\FileParser;

use App\Services\TransactionFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractFileParser
{
    const FILE_TYPE_PDF = 'pdf';
    const FILE_TYPE_CSV = 'csv';

    protected $transactionFactory;

    protected $params;

    public function __construct(TransactionFactory $transactionFactory, ParameterBagInterface $params)
    {
        $this->transactionFactory = $transactionFactory;
        $this->params = $params;
    }

    abstract public function getName(): string;
    abstract public function getFileType(): string;
    abstract public function getLabel(): string;
    abstract public function parse(string $filePath, ?string $accountName): array;
}
