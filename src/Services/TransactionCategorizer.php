<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionsCategorizedEvent;
use Doctrine\ORM\EntityManagerInterface;
use React\EventLoop\LoopInterface;
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

    function categorizeOne($transaction)
    {
        $subCategory = $this->ruleChecker->getMatchingSubCategory($transaction);
        if ($subCategory) {
            $transaction->setSubCategory($subCategory);
            $this->entityManager->persist($transaction);
            $this->dispatcher->dispatch(
                new TransactionCategorizedEvent($transaction),
                TransactionCategorizedEvent::NAME
            );

            return $transaction;
        }

        return null;
    }

    public function categorizeAllSync()
    {
        $uncategorizedTransactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findUncategorizedTransactions()
        ;

        $categorizedTransactions = [];

        foreach ($uncategorizedTransactions as $transaction) {
            $categorizedTransaction = $this->categorizeOne($transaction);
            if ($categorizedTransaction) {
                $categorizedTransactions[] = $transaction;
            }
        }

        $this->entityManager->flush();

        return $categorizedTransactions;
    }

    public function categorizeInNextTick($loop, $transactions)
    {
        $loop->futureTick(function() use ($loop, $transactions) {
            if (count($transactions) > 0) {
                $transaction = array_pop($transactions);
                $this->categorizeOne($transaction);
                $this->categorizeInNextTick($loop, $transactions);
            } else {
                $this->entityManager->flush();
                $this->dispatcher->dispatch(
                    new TransactionsCategorizedEvent(),
                    TransactionsCategorizedEvent::NAME
                );
            }
        });
    }

    public function categorizeAllAsync(LoopInterface $loop)
    {
        $this->ruleChecker->setRules();

        $uncategorizedTransactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findUncategorizedTransactions()
        ;

        $this->categorizeInNextTick($loop, $uncategorizedTransactions);
    }
}