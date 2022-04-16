<?php

namespace App\Services\FileParser;

class CaisseEpargneAccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'caisse-epargne';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_PDF;
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
