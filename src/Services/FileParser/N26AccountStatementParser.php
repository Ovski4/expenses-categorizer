<?php

namespace App\Services\FileParser;

class N26AccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'n26';
    }

    public function getLabel(): string
    {
        return 'N26 account statement';
    }
}
