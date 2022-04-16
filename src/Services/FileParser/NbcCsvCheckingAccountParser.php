<?php

namespace App\Services\FileParser;

use AccountGuessable;
use App\Entity\Account;
use App\Services\TransactionFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class NbcCsvCheckingAccountParser extends AbstractAccountStatementParser implements AccountGuessable
{
    private SluggerInterface $slugger;

    public function __construct(
        TransactionFactory $transactionFactory,
        ParameterBagInterface $params,
        SluggerInterface $slugger
    )
    {
        parent::__construct($transactionFactory, $params);

        $this->slugger = $slugger;
    }

    public function getName(): string
    {
        return 'nbc-checking';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_CSV;
    }

    public function getLabel(): string
    {
        return 'NBC checking account csv export';
    }

    public function extractsAccountsFromFile(): bool
    {
        return false;
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
