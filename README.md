Expenses categorizer
====================

An application written in PHP (Symfony 4 framework) used to import, categorize and analyze transactions.

Core values
-----------

* Being able to categorize transactions from a monthly account statement in seconds, not minutes (obviously initial setup will take longer).
* Build every chart one can think of to make sense of expenses and revenues.

Workflow
--------

1. Import transactions from files (this application + third party parsers)
2. Categorize transactions (this application)
3. Export and analyze the transactions. (no need to reinvent the wheel : elasticsearch & kibana are perfect for this)

![Kibana dashboard](docs/kibana-dashboard.png "Kibana dashboard")

Getting started
---------------

Run the app:

```bash
docker-compose up -d
# migrations are run as soon as the php container get started
```

Run the tests:

```bash
docker-compose run php php bin/phpunit
```

Create new parsers
------------------

The current implementation can import transactions from account statements coming from the following banks:
 * Crédit Mutuel
 * Caisse d'épargne

To add a new file parser, create a new class that implements **AbstractFileParser**.

You will have to implement the following methods:
 * parse() : returns an array of Transactions object.
 * getName() : returns a string that will appear in urls
 * getLabel() : returns a string that will be used in forms and alerts (a human readable string).

Here is an example below:

```php
<?php

namespace App\Services\FileParser;

use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Transaction;

class HelloBankAccountStatementParser extends AbstractFileParser 
{
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function getName(): string
    {
        return 'hello-bank';
    }

    public function getLabel(): string
    {
        return 'Hello Bank account statement';
    }

    public function parse(string $filepath): array
    {
        $results = ... // do your magic here with the file

        $transactions = [];

        foreach($results as $result) {
            
            $transaction = new Transaction();
            $transaction
                ->setAmount($result['value'])
                ->setCreatedAt(
                    (\DateTime::createFromFormat('d/m/Y', $result['date']))->setTime(0, 0, 0)
                )
                ->setLabel($result['label'])
                ->setAccount($this->accountRepository->findByAliasOrName($result['account']))
            ;

            $transactions[] = $transaction;

        }

        return $transactions;
    }
}

```
