<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionMatchesMultipleRulesEvent;
use App\Event\TransactionsCategorizedEvent;
use App\Exception\TransactionMatchesMultipleRulesException;
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
        try {
            $subCategory = $this->ruleChecker->getMatchingSubCategory($transaction);
        } catch(TransactionMatchesMultipleRulesException $e) {
            $this->dispatcher->dispatch(
                new TransactionMatchesMultipleRulesEvent($e->getTransaction(), $e->getRules()),
                TransactionMatchesMultipleRulesEvent::NAME
            );

            $subCategory = null;
        }

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

        foreach ($uncategorizedTransactions as $transaction) {
            $this->categorizeOne($transaction);
        }

        $this->entityManager->flush();
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
        if ($this->entityManager->getConnection()->ping() === false) {
            $this->entityManager->getConnection()->close();
            $this->entityManager->getConnection()->connect();
        }

        $this->entityManager->clear();
        $this->ruleChecker->setRules();

        $uncategorizedTransactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findUncategorizedTransactions()
        ;

        $this->categorizeInNextTick($loop, $uncategorizedTransactions);
    }
}
