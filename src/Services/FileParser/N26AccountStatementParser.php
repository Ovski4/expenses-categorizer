<?php

namespace App\Services\FileParser;

class N26AccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'n26';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_PDF;
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
