<?php

namespace App\Services\FileParser;

use App\Services\TransactionFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractAccountStatementParser extends AbstractFileParser
{
    protected HttpClientInterface $httpClient;

    public function __construct(
        TransactionFactory $transactionFactory,
        ParameterBagInterface $params,
        HttpClientInterface $httpClient
    ) {
        parent::__construct($transactionFactory, $params);
        $this->httpClient = $httpClient;
    }

    public function parse(string $filepath, array $options): array
    {
        $resolvedOptions = $this->resolver->resolve($options);
        $accountStatementParserApiUrl = $this->params->get('app.account_statement_parser_api_url');

        $response = $this->httpClient->request(
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

            $transaction = $this->transactionFactory->createFromArray($result);

            if ($transaction->getAmount() != 0) {
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }
}
