<?php

namespace App\Services;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;

class TransactionExporter
{
    private $entityManager;

    private $client;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();;
    }

    public function exportAll()
    {
        $this->createIndexIfNotExists();

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['created_at'=>'asc'])
        ;

        $createdTransactions = [];
        $updatedTransactions = [];

        foreach ($transactions as $transaction) {
            $params = [
                'index' => 'transactions',
                'id'    => $transaction->getId(),
                'body'  => $transaction->toArray()
            ];

            $response = $this->client->index($params);

            if ($response['result'] === 'created') {
                $createdTransactions[] = $transaction;
            } else if ($response['result'] === 'updated') {
                $updatedTransactions[] = $transaction;
            } else {
                throw new \Exception('Error creating or updating transaction');
            }
        }

        return [
            'total_transactions_count' => count($transactions),
            'created_transactions_count' => count($createdTransactions),
            'updated_transactions_count' => count($updatedTransactions)
        ];
    }

    private function createIndexIfNotExists()
    {
        $indexParams['index']  = 'transactions';
        if ($this->client->indices()->exists($indexParams)) {
            return;
        }

        $params = [
            'index' => 'transactions',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'label' => [
                            'type' => 'text'
                        ],
                        'created_at' => [
                            'type' => 'date'
                        ],
                        'account' => [
                            'type' => 'keyword'
                        ],
                        'sub_category' => [
                            'type' => 'keyword'
                        ],
                        'top_category' => [
                            'type' => 'keyword'
                        ],
                        'currency' => [
                            'type' => 'keyword'
                        ],
                        'type' => [
                            'type' => 'keyword'
                        ],
                        'amount' => [
                            'type' => 'float'
                        ]
                    ]
                ]
            ]
        ];

        $this->client->indices()->create($params);
    }
}
