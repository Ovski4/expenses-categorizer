<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Event\TransactionCategorizedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransactionCategorizer
{
    private $ruleChecker;
    private $entityManager;
    private $dispatcher;

    public function __construct(
        RuleChecker $ruleChecker,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->ruleChecker = $ruleChecker;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function categorizeAll()
    {
        $uncategorizedTransactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findUncategorizedTransactions()
        ;

        $transactions = [];

        foreach ($uncategorizedTransactions as $transaction) {
            $subCategory = $this->ruleChecker->getMatchingSubCategory($transaction);
            if ($subCategory) {
                $transaction->setSubCategory($subCategory);
                $this->entityManager->persist($transaction);
                $transactions[] = $transaction;
                $this->dispatcher->dispatch(
                    new TransactionCategorizedEvent($transaction),
                    TransactionCategorizedEvent::NAME
                );
            }
        }

        $this->entityManager->flush();

        return $transactions;
    }
}
