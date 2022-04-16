<?php

namespace App\Services\FileParser;

use App\Entity\Account;

class NbcCsvCreditAccountParser extends NbcCsvAccountParser
{
    public function getName(): string
    {
        return 'nbc-credit';
    }

    public function getLabel(): string
    {
        return 'NBC credit account csv file';
    }

    public function matches(Account $account): bool
    {
        $slugifiedAccountName = $this->slugger
            ->slug($account->getName())
            ->lower()
            ->toString()
        ;

        // Return true if "credit" is present in the slugified account name.
        if (strpos($slugifiedAccountName, 'credit') !== false) {
            return true;
        }

        return false;
    }
}
