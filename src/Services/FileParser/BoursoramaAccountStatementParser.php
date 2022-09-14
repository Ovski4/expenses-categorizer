<?php

namespace App\Services\FileParser;

use App\Services\FileParser\Traits\PdfFileParserTrait;

class BoursoramaAccountStatementParser extends AbstractAccountStatementParser
{
    use PdfFileParserTrait;

    public function getName(): string
    {
        return 'boursorama';
    }

    public function getLabel(): string
    {
        return 'Boursorama account statement';
    }

    public function extractsAccountsFromFile(): bool
    {
        return true;
    }
}
