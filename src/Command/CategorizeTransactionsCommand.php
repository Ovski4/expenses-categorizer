<?php

namespace App\Command;

use App\Event\TransactionCategorizedEvent;
use App\Services\TransactionCategorizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategorizeTransactionsCommand extends Command implements EventSubscriberInterface
{
    protected static $defaultName = 'app:categorize-transactions';

    private $transactionCategorizer;
    private $output;

    public function __construct(TransactionCategorizer $transactionCategorizer)
    {
        $this->transactionCategorizer = $transactionCategorizer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Categorize transactions from rules')
            ->setHelp('This command sets sub categories on transactions when rule matches')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->transactionCategorizer->categorizeAllSync();
    }

    public static function getSubscribedEvents()
    {
        return [
            TransactionCategorizedEvent::NAME => 'onTransactionCategorized'
        ];
    }

    public function onTransactionCategorized(TransactionCategorizedEvent $event)
    {
        if ($this->output) {
            $this->output->writeln(sprintf(
                'Found sub category <info>%s</info> for transaction <info>%s</info> (le <comment>%s</comment> -> <comment>%s</comment> euros)',
                $event->getTransaction()->getSubCategory()->getName(),
                $event->getTransaction()->getLabel(),
                $event->getTransaction()->getCreatedAt()->format('Y-m-d H:i:s'),
                $event->getTransaction()->getAmount()
            ));
        }
    }
}
