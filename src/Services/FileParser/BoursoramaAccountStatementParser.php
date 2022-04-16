<?php

namespace App\Services\FileParser;

class BoursoramaAccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'boursorama';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_PDF;
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
