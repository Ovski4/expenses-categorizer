<?php

namespace App\Exception;

class AccountNotFoundException extends \Exception
{
    private $search;

    public function __construct(string $search)
    {
        $this->search = $search;

        parent::__construct('Account with alias or name "%search%" was not found');
    }

    public function getAccountSearch()
    {
        return $this->search;
    }
}
