<?php

namespace App\Exception;

class AccountNotFoundException extends \Exception
{
    private string $search;

    public function __construct(string $search)
    {
        $this->search = $search;

        parent::__construct('Account with alias or name "%search%" was not found');
    }

    public function getAccountSearch(): string
    {
        return $this->search;
    }
}
