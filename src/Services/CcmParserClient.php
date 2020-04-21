<?php

namespace App\Services;

use Symfony\Component\HttpClient\HttpClient;

class CcmParserClient
{
    private $transactionFactory;

    public function __construct(TransactionFactory $transactionFactory)
    {
        $this->transactionFactory = $transactionFactory;
    }

    public function parse(string $filepath)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://ccm_parser?statement=' . $filepath);
        $results = json_decode($response->getContent(), true);

        $transactions = [];

        foreach($results as $result) {
            $transactions[] = $this->transactionFactory->createFromArray($result);
        }

        return $transactions;
    }
}
