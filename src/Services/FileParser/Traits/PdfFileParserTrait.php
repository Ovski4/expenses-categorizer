<?php

namespace App\Services\FileParser\Traits;

use App\Services\FileParser\AbstractFileParser;

trait PdfFileParserTrait
{
    public function getAllowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/x-pdf',
        ];
    }

    public function getFileType(): string
    {
        return AbstractFileParser::FILE_TYPE_PDF;
    }
}
