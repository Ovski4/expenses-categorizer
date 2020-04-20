<?php

namespace App\Services;

use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionExportedEvent;
use App\Event\TransactionsCategorizedEvent;
use App\Event\TransactionsExportedEvent;
use App\Event\TransactionsExportingEvent;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebSocketMessaging implements MessageComponentInterface, EventSubscriberInterface
{
    private $categorizingTransactionsClients;
    private $exportingTransactionsClients;
    private $transactionCategorizer;
    private $transactionExporter;
    private $loop;

    public function __construct(
        TransactionCategorizer $transactionCategorizer,
        TransactionExporter $transactionExporter
    ) {
        $this->categorizingTransactionsClients = new \SplObjectStorage();
        $this->exportingTransactionsClients = new \SplObjectStorage();
        $this->transactionCategorizer = $transactionCategorizer;
        $this->transactionExporter = $transactionExporter;
    }

    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function onOpen(ConnectionInterface $conn) {}

    public function onClose(ConnectionInterface $closedConnection)
    {
        if ($this->categorizingTransactionsClients->contains($closedConnection)) {
            $this->categorizingTransactionsClients->detach($closedConnection);
        } else if ($this->exportingTransactionsClients->contains($closedConnection)) {
            $this->exportingTransactionsClients->detach($closedConnection);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send('An error has occurred: ' . $e->getMessage());
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, $message)
    {
        if ($message === 'categorize_transactions') {
            $this->categorizingTransactionsClients->attach($conn);
            $this->transactionCategorizer->categorizeAllAsync($this->loop);
        }

        if ($message === 'export_transactions') {
            $this->exportingTransactionsClients->attach($conn);
            try {
                $this->transactionExporter->exportAllAsync($this->loop);
            } catch(NoNodesAvailableException $e) {
                $conn->send(json_encode([
                    'topic' => 'error',
                    'data' => 'Elasticsearch seems to be down'
                ]));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionCategorizedEvent::NAME => 'onTransactionCategorized',
            TransactionsCategorizedEvent::NAME => 'onTransactionsCategorized',
            TransactionExportedEvent::NAME => 'onTransactionExported',
            TransactionsExportedEvent::NAME => 'onTransactionsExported',
            TransactionsExportingEvent::NAME => 'onTransactionsExporting'
        ];
    }

    public function onTransactionCategorized(TransactionCategorizedEvent $event)
    {
        foreach ($this->categorizingTransactionsClients as $conn) {
            $conn->send(json_encode([
                'topic' => 'single_transaction.categorized',
                'data' => $event->getTransaction()->toArray()
            ]));
        }
    }

    public function onTransactionExported(TransactionExportedEvent $event)
    {
        foreach ($this->exportingTransactionsClients as $conn) {
            $conn->send(json_encode([
                'topic' => 'single_transaction.exported',
                'data' => $event->getResponse()
            ]));
        }
    }

    public function onTransactionsCategorized()
    {
        foreach ($this->categorizingTransactionsClients as $conn) {
            $conn->send(json_encode([
                'topic' => 'transactions.categorized'
            ]));
        }
    }

    public function onTransactionsExported()
    {
        foreach ($this->exportingTransactionsClients as $conn) {
            $conn->send(json_encode([
                'topic' => 'transactions.exported'
            ]));
        }
    }

    public function onTransactionsExporting(TransactionsExportingEvent $event)
    {
        foreach ($this->exportingTransactionsClients as $conn) {
            $conn->send(json_encode([
                'topic' => 'transactions.exporting',
                'data' => $event->getTransactionCount()
            ]));
        }
    }
}
