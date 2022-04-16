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
            ->setAllowedTypes('accountId', ['string', 'null'])
        ;
    }

    /**
     * The file mime-types allowed for this parser.
     */
    abstract public function getAllowedMimeTypes(): array;

    /**
     * Get the name of the file parser.
     * This will be used in urls.
     */
    abstract public function getName(): string;

    /**
     * The label of the file parsed by this parser.
     * This will be used in forms and alerts (a human readable string).
     */
    abstract public function getLabel(): string;

    /**
     * The type of the file parsed by this parser (csv, pdf...)
     */
    abstract public function getFileType(): string;

    /**
     * Whether or not this parser extract the accounts from the file.
     * If not, the user will have to manually specify the account transactions will be imported into.
     */
    abstract public function extractsAccountsFromFile(): bool;

    /**
     * Use this method to add the parser logic.
     */
    abstract public function parse(string $filePath, array $options): array;
}
