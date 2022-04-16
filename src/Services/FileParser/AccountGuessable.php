<?php

use App\Entity\Account;

/**
 * File parser that are able to guess if an account can leverage
 * the parser to import transactions, may implement this interface.
 */
interface AccountGuessable
{
    public function matches(Account $account): bool;
}
