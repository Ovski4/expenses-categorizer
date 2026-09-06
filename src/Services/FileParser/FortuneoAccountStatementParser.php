<?php

namespace App\Services\FileParser;

use App\Services\FileParser\Traits\PdfFileParserTrait;

class FortuneoAccountStatementParser extends AbstractAccountStatementParser
{
    use PdfFileParserTrait;

    public function getName(): string
    {
        return 'fortuneo';
    }

    public function getLabel(): string
    {
        return 'Fortuneo account statement';
    }

    public function extractsAccountsFromFile(): bool
    {
        return true;
    }
}
