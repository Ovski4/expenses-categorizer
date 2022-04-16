<?php

namespace App\Services\FileParser;

use AccountGuessable;
use App\Entity\Account;
use App\Services\TransactionFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class NbcCsvCreditAccountParser extends AbstractAccountStatementParser implements AccountGuessable
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
        return 'nbc-credit';
    }

    public function getFileType(): string
    {
        return self::FILE_TYPE_CSV;
    }

    public function getLabel(): string
    {
        return 'NBC credit account csv export';
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

        // Return true if "credit" is present in the slugified account name.
        if (strpos($slugifiedAccountName, 'credit') !== false) {
            return true;
        }

        return false;
    }
}
