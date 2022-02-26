<?php

namespace App\Services\FileParser;

class CreditMutuelAccountStatementParser extends AbstractAccountStatementParser
{
    public function getName(): string
    {
        return 'credit-mutuel';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_PDF;
    }

    public function getLabel(): string
    {
        return 'Crédit Mutuel account statement';
    }
}
