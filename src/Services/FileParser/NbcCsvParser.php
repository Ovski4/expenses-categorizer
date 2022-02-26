<?php

namespace App\Services\FileParser;

class NbcCsvParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'nbc';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_CSV;
    }

    public function getLabel(): string
    {
        return 'NBC csv export';
    }
}
