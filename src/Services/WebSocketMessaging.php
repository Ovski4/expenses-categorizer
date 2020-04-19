<?php

namespace App\Services;

use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionsCategorizedEvent;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebSocketMessaging implements MessageComponentInterface, EventSubscriberInterface
{
    private $categorizingTransactionsClients;
    private $loop;

    public function __construct(TransactionCategorizer $transactionCategorizer)
    {
        $this->categorizingTransactionsClients = new \SplObjectStorage();
        $this->transactionCategorizer = $transactionCategorizer;
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
            $this->transactionCategorizer->categorizeAllAsync($this->loop);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionCategorizedEvent::NAME => 'onTransactionCategorized',
            TransactionsCategorizedEvent::NAME => 'onTransactionsCategorized'
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

    public function onTransactionsCategorized()
    {
        foreach ($this->categorizingTransactionsClients as $conn) {
            echo "all categorized\n";
            $conn->send(json_encode([
                'topic' => 'transactions.categorized'
            ]));
        }
    }
}
