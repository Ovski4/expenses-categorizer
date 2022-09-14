<?php

namespace App\Services\FileParser;

use App\Entity\Account;

class NbcCsvCheckingAccountParser extends NbcCsvAccountParser
{
    public function getName(): string
    {
        return 'nbc-checking';
    }

    public function getLabel(): string
    {
        return 'NBC checking account csv file';
    }

    public function matches(Account $account): bool
    {
        $slugifiedAccountName = $this->slugger
            ->slug($account->getName())
            ->lower()
            ->toString()
        ;

        // Return true if "check" or "cheque" is present in the slugified account name.
        foreach(['check', 'cheque'] as $keyword) {
            if (strpos($slugifiedAccountName, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
