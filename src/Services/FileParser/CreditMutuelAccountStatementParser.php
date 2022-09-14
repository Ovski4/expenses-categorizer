<?php

namespace App\Services\FileParser;

use App\Services\FileParser\Traits\PdfFileParserTrait;

class CreditMutuelAccountStatementParser extends AbstractAccountStatementParser
{
    use PdfFileParserTrait;

    public function getName(): string
    {
        return 'credit-mutuel';
    }

    public function getLabel(): string
    {
        return 'Crédit Mutuel account statement';
    }

    public function extractsAccountsFromFile(): bool
    {
        return true;
    }
}
