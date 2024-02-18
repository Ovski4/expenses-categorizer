<?php

namespace App\Services\Exporter;

use App\Entity\Transaction;
use App\Event\TransactionExportedEvent;
use App\Event\TransactionsExportedEvent;
use App\Event\TransactionsExportingEvent;
use App\Services\ConnectionKeeper;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;
use React\EventLoop\LoopInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ElasticsearchExporter
{
    private string $elasticsearchHost;
    private string $elasticsearchIndex;
    private $entityManager;
    private $client;
    private $dispatcher;
    private $connectionKeeper;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        ConnectionKeeper $connectionKeeper
    )
    {
        $this->elasticsearchHost = $params->get('app.elasticsearch_host');
        $this->elasticsearchIndex = $params->get('app.elasticsearch_index');
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->connectionKeeper = $connectionKeeper;
    }

    function exportOne($transaction)
    {
        $body = $transaction->toArray();
        unset($body['id']);

        $params = [
            'index' => $this->elasticsearchIndex,
            'id'    => $transaction->getId(),
            'body'  => $body
        ];

        $response = $this->client->index($params);

        if (!in_array($response['result'], ['created', 'updated'])) {
            throw new \Exception('Error creating or updating transaction');
        }

        $this->dispatcher->dispatch(
            new TransactionExportedEvent($transaction, $response),
            TransactionExportedEvent::NAME
        );
    }

    public function exportAllSync()
    {
        $this->client = ClientBuilder::create()->setHosts([$this->elasticsearchHost])->build();
        $this->createIndexIfNotExists();

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy(['toSyncInElasticsearch' => true])
        ;

        $this->dispatcher->dispatch(
            new TransactionsExportingEvent(count($transactions)),
            TransactionsExportingEvent::NAME
        );

        foreach ($transactions as $transaction) {
            $this->exportOne($transaction);
        }

        $this->dispatcher->dispatch(
            new TransactionsExportedEvent(),
            TransactionsExportedEvent::NAME
        );
    }

    public function exportInNextTick($loop, $transactions, array $listeners)
    {
        $loop->futureTick(function() use ($loop, $transactions, $listeners) {
            if (count($transactions) > 0) {
                $transaction = array_pop($transactions);
                $this->exportOne($transaction);
                $this->exportInNextTick($loop, $transactions, $listeners);
            } else {
                $this->dispatcher->dispatch(
                    new TransactionsExportedEvent(),
                    TransactionsExportedEvent::NAME
                );

                foreach( $listeners as $eventName => $listener ) {
                    $this->dispatcher->removeListener($eventName, $listener);
                }
            }
        });
    }

    public function exportAllAsync(LoopInterface $loop, array $listeners)
    {
        $this->client = ClientBuilder::create()->setHosts([$this->elasticsearchHost])->build();
        $this->createIndexIfNotExists();

        $this->connectionKeeper->keepAlive();
        $this->entityManager->clear();

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy(['toSyncInElasticsearch' => true])
        ;

        $this->dispatcher->dispatch(
            new TransactionsExportingEvent(count($transactions)),
            TransactionsExportingEvent::NAME
        );

        $this->exportInNextTick($loop, $transactions, $listeners);
    }

    private function createIndexIfNotExists()
    {
        $indexParams['index']  = $this->elasticsearchIndex;
        if ($this->client->indices()->exists($indexParams)) {
            return;
        }

        $params = [
            'index' => $this->elasticsearchIndex,
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
