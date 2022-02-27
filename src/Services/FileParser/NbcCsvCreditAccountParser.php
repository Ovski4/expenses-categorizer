<?php

namespace App\Services\FileParser;

class NbcCsvCreditAccountParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'nbc-credit';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_CSV;
    }

    public function getLabel(): string
    {
        return 'NBC credit account csv export';
    }
}
