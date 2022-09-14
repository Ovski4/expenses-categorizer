<?php

namespace App\Services\FileParser;

use App\Services\FileParser\Traits\CsvFileParserTrait;
use App\Services\TransactionFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

abstract class NbcCsvAccountParser extends AbstractAccountStatementParser implements AccountGuessable
{
    use CsvFileParserTrait;

    protected SluggerInterface $slugger;

    public function __construct(
        TransactionFactory $transactionFactory,
        ParameterBagInterface $params,
        SluggerInterface $slugger
    )
    {
        parent::__construct($transactionFactory, $params);

        $this->slugger = $slugger;
    }

    public function extractsAccountsFromFile(): bool
    {
        return false;
    }
}
