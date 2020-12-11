<?php

namespace App\Services\FileParser;

class BoursoramaAccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'boursorama';
    }

    public function getLabel(): string
    {
        return 'Boursorama account statement';
    }
}
