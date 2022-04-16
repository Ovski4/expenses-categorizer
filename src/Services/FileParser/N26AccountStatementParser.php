<?php

namespace App\Services\FileParser;

use App\Services\FileParser\Traits\PdfFileParserTrait;

class N26AccountStatementParser extends AbstractAccountStatementParser
{
    use PdfFileParserTrait;

    public function getName(): string
    {
        return 'n26';
    }

    public function getLabel(): string
    {
        return 'N26 account statement';
    }

    public function extractsAccountsFromFile(): bool
    {
        return true;
    }
}
