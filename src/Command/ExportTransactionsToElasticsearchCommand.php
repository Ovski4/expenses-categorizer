<?php

namespace App\Command;

use App\Services\TransactionExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTransactionsToElasticsearchCommand extends Command
{
    protected static $defaultName = 'app:export-transactions';

    private $exporter;

    public function __construct(TransactionExporter $exporter)
    {
        $this->exporter = $exporter;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Export transactions to elasticsearch')
            ->setHelp('This command sends transactions in elasticsearch')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->exporter->exportAllSync();
        $output->writeln(sprintf(
            '<info>%s</info> transactions indexed (<comment>%s</comment> created, <comment>%s</comment> updated)',
            $results['total_transactions_count'],
            $results['created_transactions_count'],
            $results['updated_transactions_count']
        ));
    }
}
