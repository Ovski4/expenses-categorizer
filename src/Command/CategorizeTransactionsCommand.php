<?php

namespace App\Command;

use App\Entity\Transaction;
use App\Services\RuleChecker;
use Doctrine\Common\Persistence\ObjectManager as EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CategorizeTransactionsCommand extends Command
{
    protected static $defaultName = 'app:categorize-transactions';

    private $entityManager;

    public function __construct(EntityManager $entityManager, RuleChecker $ruleChecker)
    {
        $this->entityManager = $entityManager;
        $this->ruleChecker = $ruleChecker;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Categorize transactions from rules')
            ->setHelp('This command set sub categories on transactions when rule matches')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uncategorizedTransactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findUncategorizedTransactions()
        ;

        foreach ($uncategorizedTransactions as $transaction) {
            $subCategory = $this->ruleChecker->getMatchingSubCategory($transaction);
            if ($subCategory) {
                $transaction->setSubCategory($subCategory);
                $output->writeln(sprintf(
                    'Found sub category %s for transaction %s (le %s - %s euros)',
                    $subCategory->getName(),
                    $transaction->getLabel(),
                    $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
                    $transaction->getAmount()

                ));
            }
        }
    }
}
