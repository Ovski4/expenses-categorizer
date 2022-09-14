Expenses categorizer
====================

![PHPUnit build Status](https://github.com/Ovski4/expenses-categorizer/actions/workflows/run-phpunit-and-build-coverage-report.yml/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/Ovski4/expenses-categorizer/badge.svg?branch=master)](https://coveralls.io/github/Ovski4/expenses-categorizer?branch=master)

An application written in PHP (Symfony 4 framework) to import, categorize and analyze transactions.

Core values
-----------

* Being able to categorize transactions from a monthly account statement in seconds, not minutes (obviously initial setup will take longer).
* Build every chart one can think of to make sense of expenses and revenues.

How to use it?
--------------

Follow [the workflow](docs/workflow.md) to get to know how to use the app.

Installation with docker
------------------------

### Run the app in dev

```bash
docker-compose up -d mysql
docker-compose logs -f mysql # wait for mysql to be ready
docker-compose up -d # doctrine migrations will run as soon as the php container get started
docker-compose run php composer install

# allow the web user from the php container to write in the /var/statements folder
docker exec -it expenses-categorizer_account_statement_parser_1 chown 1000 -R /var/statements

# optionally, create some default transaction categories
docker-compose run php php bin/console doctrine:fixtures:load --append
```

Browse [http://localhost](http://localhost)

### Run the app in prod

To run the app in prod, prefix all above docker-compose commands with `-f docker-compose-prod.yml`.

Update the services environment variables in the docker-compose-prod.yml file according to your needs.

Build the images

```bash
docker build -f docker/build/php/Dockerfile -t ovski/expenses-categorizer-php:latest .
docker build -f docker/build/nginx/Dockerfile -t ovski/expenses-categorizer-nginx:latest .
```

### Run the tests

```bash
docker-compose run php php bin/phpunit
```

Create new parsers
------------------

The current implementation can import transactions from french account statements coming from the following banks:
 * Crédit Mutuel (parser service here)
 * Caisse d'épargne
 * N26
 * Boursorama

Source code for these parsers can be found at https://github.com/Ovski4/account-statement-parsers.

To add a new file parser, create a new class that implements **AbstractFileParser**. The symfony framework will take care of creating tagged services automatically and update the user interface (the forms) accordingly.

You will have to implement the following methods:
 * parse() : returns an array of [Transactions objects](src/Entity/Transaction.php).
 * getName() : returns a string that will appear in urls
 * getLabel() : returns a string that will be used in forms and alerts (a human readable string).

Here is an example below:

```php
<?php

namespace App\Services\FileParser;

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

    public function parse(string $filepath, array $options = []): array
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
