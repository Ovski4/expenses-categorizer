<?php

namespace App\Services\WebSocketMessageHandler;

use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionCategoryChangedEvent;
use App\Event\TransactionMatchesMultipleRulesEvent;
use App\Event\TransactionsCategorizedEvent;
use App\Services\TransactionCategorizer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategorizeTransactionsHandler extends AbstractWebSocketMessageHandler implements EventSubscriberInterface
{
    private $transactionCategorizer;

    public function __construct(TransactionCategorizer $transactionCategorizer)
    {
        $this->transactionCategorizer = $transactionCategorizer;

        parent::__construct();
    }

    public function doHandle(ConnectionInterface $connection, LoopInterface $loop)
    {
        $this->transactionCategorizer->categorizeAllAsync($loop);
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionCategorizedEvent::NAME => 'onTransactionCategorized',
            TransactionsCategorizedEvent::NAME => 'onTransactionsCategorized',
            TransactionMatchesMultipleRulesEvent::NAME => 'onTransactionMatchesMultipleRules',
            TransactionCategoryChangedEvent::NAME => 'onTransactionCategoryChanged'
        ];
    }

    public function onTransactionCategorized(TransactionCategorizedEvent $event)
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'single_transaction.categorized', $event->getTransaction()->toArray());
        }
    }

    public function onTransactionCategoryChanged(TransactionCategoryChangedEvent $event)
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage(
                $connection,
                'single_transaction.category_changed',
                [
                    'transaction' => $event->getTransaction()->toArray(),
                    'old_sub_category' => $event->getOldSubCategory()->getName()
                ]
            );
        }
    }

    public function onTransactionsCategorized()
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'transactions.categorized');
        }
    }

    public function onTransactionMatchesMultipleRules(TransactionMatchesMultipleRulesEvent $event)
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage(
                $connection,
                'single_transaction.matches_multiple_rules',
                [
                    'transaction' => $event->getTransaction()->toArray(),
                    'rules' => array_map(function($rule) {
                        return $rule->toArray();
                    }, $event->getRules())
                ]
            );
        }
    }
}
