<?php

namespace App\Services\FileParser;

class CaisseEpargneAccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'caisse-epargne';
    }

    public function getLabel(): string
    {
        return 'Caisse d\'épargne account statement';
    }
}
