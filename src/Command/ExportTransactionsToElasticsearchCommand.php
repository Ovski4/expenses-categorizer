<?php

namespace App\Command;

use App\Event\TransactionExportedEvent;
use App\Event\TransactionsExportedEvent;
use App\Services\Exporter\ElasticsearchExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExportTransactionsToElasticsearchCommand extends Command
{
    protected static $defaultName = 'app:export-transactions';

    private ElasticsearchExporter $exporter;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        ElasticsearchExporter $exporter,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->exporter = $exporter;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Export transactions to elasticsearch')
            ->setHelp('This command sends transactions in elasticsearch')
        ;
    }

    private function onTransactionExported(TransactionExportedEvent $event, OutputInterface $output)
    {
        $transaction = $event->getTransaction();
        $transaction->setToSyncInElasticsearch(false);

        $this->entityManager->persist($transaction);

        $output->writeln(sprintf('Transaction <info>%s</info> exported', $transaction->getId()));
    }

    private function onTransactionsExported(OutputInterface $output)
    {
        $this->entityManager->flush();

        $output->writeln('All done!');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dispatcher->addListener(
            TransactionExportedEvent::NAME,
            fn (TransactionExportedEvent $event) => $this->onTransactionExported($event, $output)
        );

        $this->dispatcher->addListener(
            TransactionsExportedEvent::NAME,
            fn () => $this->onTransactionsExported($output)
        );

        $this->exporter->exportAllSync();

        return self::SUCCESS;
    }
}
