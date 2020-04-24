<?php

namespace App\Exception;

class AccountNotFoundException extends \Exception
{
    private $search;

    public function __construct($search)
    {
        $this->search = $search;

        parent::__construct(sprintf(
            'Account with alias or name "%s" was not found',
            $search
        ));
    }

    public function getAccountSearch()
    {
        return $this->search;
    }
}
