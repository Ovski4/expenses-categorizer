<?php

namespace App\Services\FileParser;

class NbcCsvParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'nbc';
    }

    public function getLabel(): string
    {
        return 'NBC csv export';
    }
}
