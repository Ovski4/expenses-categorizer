<?php

namespace App\Services\FileParser;

use App\Services\FileParser\Traits\PdfFileParserTrait;

class CaisseEpargneAccountStatementParser extends AbstractAccountStatementParser
{
    use PdfFileParserTrait;

    public function getName(): string
    {
        return 'caisse-epargne';
    }

    public function getLabel(): string
    {
        return 'Caisse d\'épargne account statement';
    }

    public function extractsAccountsFromFile(): bool
    {
        return true;
    }
}
