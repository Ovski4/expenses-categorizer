<?php

namespace App\Services\FileParser;

class NbcCsvCheckingAccountParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'nbc-checking';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_CSV;
    }

    public function getLabel(): string
    {
        return 'NBC checking account csv export';
    }
}
