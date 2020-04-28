<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Event\TransactionExportedEvent;
use App\Event\TransactionsExportedEvent;
use App\Event\TransactionsExportingEvent;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransactionExporter
{
    private $entityManager;
    private $client;
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    function exportOne($transaction)
    {
        $params = [
            'index' => 'transactions',
            'id'    => $transaction->getId(),
            'body'  => $transaction->toArray()
        ];

        return $this->client->index($params);
    }

    public function exportAllSync()
    {
        $this->client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
        $this->createIndexIfNotExists();

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['created_at'=>'asc'])
        ;

        $this->dispatcher->dispatch(
            new TransactionsExportingEvent(count($transactions)),
            TransactionsExportingEvent::NAME
        );

        $createdTransactions = [];
        $updatedTransactions = [];

        foreach ($transactions as $transaction) {
            $response = $this->exportOne($transaction);
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

    public function exportInNextTick($loop, $transactions)
    {
        $loop->futureTick(function() use ($loop, $transactions) {
            if (count($transactions) > 0) {
                $transaction = array_pop($transactions);
                $response = $this->exportOne($transaction);
                if (!in_array($response['result'], ['created', 'updated'])) {
                    throw new \Exception('Error creating or updating transaction');
                }
                $this->dispatcher->dispatch(
                    new TransactionExportedEvent($response),
                    TransactionExportedEvent::NAME
                );
                $this->exportInNextTick($loop, $transactions);
            } else {
                $this->dispatcher->dispatch(
                    new TransactionsExportedEvent(),
                    TransactionsExportedEvent::NAME
                );
            }
        });
    }

    public function exportAllAsync(LoopInterface $loop)
    {
        $this->client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
        $this->createIndexIfNotExists();

        if (false == $this->entityManager->getConnection()->ping()) {
            $this->entityManager->getConnection()->close();
            $this->entityManager->getConnection()->connect();
        }

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['created_at'=>'asc'])
        ;

        $this->dispatcher->dispatch(
            new TransactionsExportingEvent(count($transactions)),
            TransactionsExportingEvent::NAME
        );

        $this->exportInNextTick($loop, $transactions);
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
