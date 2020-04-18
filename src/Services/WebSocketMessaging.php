<?php

namespace App\Services;

use App\Event\TransactionCategorizedEvent;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebSocketMessaging implements MessageComponentInterface, EventSubscriberInterface
{
    private $categorizingTransactionsClients;

    public function __construct(TransactionCategorizer $transactionCategorizer)
    {
        $this->categorizingTransactionsClients = new \SplObjectStorage();
        $this->transactionCategorizer = $transactionCategorizer;
    }

    public function onOpen(ConnectionInterface $conn) {}

    public function onClose(ConnectionInterface $closedConnection)
    {
        if ($this->categorizingTransactionsClients->contains($closedConnection)) {
            $this->categorizingTransactionsClients->detach($closedConnection);
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
            $conn->send(json_encode([
                'topic' => 'transactions.categorizing'
            ]));
            $this->transactionCategorizer->categorizeAll();
            $conn->send(json_encode([
                'topic' => 'transactions.categorized'
            ]));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionCategorizedEvent::NAME => 'onTransactionCategorized'
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
}
