<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;

class AccountStatementParserClient
{
    private $transactionFactory;

    private $accountStatementParserApiUrl;

    public function __construct(TransactionFactory $transactionFactory, ParameterBagInterface $params)
    {
        $this->transactionFactory = $transactionFactory;
        $this->accountStatementParserApiUrl = $params->get('app.account_statement_parser_api_url');
    }

    public function parse(string $filepath, string $parserName)
    {
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            sprintf(
                'http://%s/%s?statement=%s',
                $this->accountStatementParserApiUrl,
                $parserName,
                $filepath
            )
        );
        $results = json_decode($response->getContent(), true);

        $transactions = [];

        foreach($results as $result) {
            $transactions[] = $this->transactionFactory->createFromArray($result);
        }

        return $transactions;
    }
}
