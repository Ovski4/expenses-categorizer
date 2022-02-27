<?php

namespace App\Services\FileParser;

use Exception;
use Symfony\Component\HttpClient\HttpClient;

abstract class AbstractAccountStatementParser extends AbstractFileParser 
{
    public function parse(string $filepath, array $options): array
    {
        $resolvedOptions = $this->resolver->resolve($options);
        $accountStatementParserApiUrl = $this->params->get('app.account_statement_parser_api_url');

        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            sprintf(
                'http://%s/%s?statement=%s',
                $accountStatementParserApiUrl,
                $this->getName(),
                $filepath
            )
        );
        $results = json_decode($response->getContent(), true);

        $transactions = [];
        foreach($results as $result) {
            if ($resolvedOptions['accountId'] !== null) {
                $result['accountId'] = $resolvedOptions['accountId'];
            }

            $transactions[] = $this->transactionFactory->createFromArray($result);
        }

        return $transactions;
    }
}
