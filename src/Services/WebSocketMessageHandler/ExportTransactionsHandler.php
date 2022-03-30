<?php

namespace App\Services\WebSocketMessageHandler;

use App\Event\TransactionExportedEvent;
use App\Event\TransactionsExportedEvent;
use App\Event\TransactionsExportingEvent;
use App\Services\Exporter\ElasticsearchExporter;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportTransactionsHandler extends AbstractWebSocketMessageHandler
{
    private $elasticsearchExporter;
    private $translator;
    private $entityManager;
    private $dispatcher;

    public function __construct(
        ElasticsearchExporter $elasticsearchExporter,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->elasticsearchExporter = $elasticsearchExporter;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    private function onTransactionExported(TransactionExportedEvent $event)
    {
        $transaction = $event->getTransaction();
        $transaction->setToSyncInElasticsearch(false);
        $this->entityManager->persist($transaction);

        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'single_transaction.exported', $event->getResponse());
        }
    }

    private function onTransactionsExported()
    {
        $this->entityManager->flush();

        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'transactions.exported');
        }
    }

    private function onTransactionsExporting(TransactionsExportingEvent $event)
    {
        foreach ($this->clients as $connection) {
            $this->sendMessage($connection, 'transactions.exporting', $event->getTransactionCount());
        }
    }

    public function doHandle(ConnectionInterface $connection, LoopInterface $loop)
    {
        $listeners = [
            TransactionExportedEvent::NAME => fn (TransactionExportedEvent $event) => $this->onTransactionExported($event),
            TransactionsExportingEvent::NAME => fn (TransactionsExportingEvent $event) => $this->onTransactionsExporting($event),
            TransactionsExportedEvent::NAME => fn () => $this->onTransactionsExported(),
        ];

        foreach( $listeners as $eventName => $listener ) {
            $this->dispatcher->addListener($eventName, $listener);
        }

        try {
            $this->elasticsearchExporter->exportAllAsync($loop, $listeners);
        } catch(NoNodesAvailableException $e) {
            $this->sendMessage($connection, 'error', $this->translator->trans('Elasticsearch seems to be down'));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TransactionExportedEvent::NAME => 'onTransactionExported',
            TransactionsExportedEvent::NAME => 'onTransactionsExported',
            TransactionsExportingEvent::NAME => 'onTransactionsExporting'
        ];
    }
}
