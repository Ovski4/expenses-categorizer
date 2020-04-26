<?php

namespace App\Services\WebSocketMessageHandler;

use App\Event\TransactionExportedEvent;
use App\Event\TransactionsExportedEvent;
use App\Event\TransactionsExportingEvent;
use App\Services\TransactionExporter;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportTransactionsHandler extends AbstractWebSocketMessageHandler implements EventSubscriberInterface
{
    private $transactionExporter;

    private $translator;

    public function __construct(TransactionExporter $transactionExporter, TranslatorInterface $translator)
    {
        $this->transactionExporter = $transactionExporter;
        $this->translator = $translator;

        parent::__construct();
    }

    public function doHandle(ConnectionInterface $connection, LoopInterface $loop)
    {
        try {
            $this->transactionExporter->exportAllAsync($loop);
        } catch(NoNodesAvailableException $e) {
            $this->sendMessage($connection, 'error', $this->translator->trans('Elasticsearch seems to be down'));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionExportedEvent::NAME => 'onTransactionExported',
            TransactionsExportedEvent::NAME => 'onTransactionsExported',
            TransactionsExportingEvent::NAME => 'onTransactionsExporting'
        ];
    }

    public function onTransactionExported(TransactionExportedEvent $event)
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'single_transaction.exported', $event->getResponse());
        }
    }

    public function onTransactionsExported()
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'transactions.exported');
        }
    }

    public function onTransactionsExporting(TransactionsExportingEvent $event)
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'transactions.exporting', $event->getTransactionCount());
        }
    }
}
