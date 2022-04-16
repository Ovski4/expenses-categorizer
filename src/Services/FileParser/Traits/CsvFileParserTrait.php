<?php

namespace App\Services\FileParser\Traits;

use App\Services\FileParser\AbstractFileParser;

trait CsvFileParserTrait
{
    public function getAllowedMimeTypes(): array
    {
        return [
            'text/csv',
            'text/plain',
        ];
    }

    public function getFileType(): string
    {
        return AbstractFileParser::FILE_TYPE_CSV;
    }
}
