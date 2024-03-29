<?php

namespace App\Services;

use App\Entity\Transaction;
use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionCategoryChangedEvent;
use App\Event\TransactionMatchesMultipleRulesEvent;
use App\Event\TransactionsCategorizedEvent;
use App\Exception\TransactionMatchesMultipleRulesException;
use App\Services\ConnectionKeeper;
use Doctrine\ORM\EntityManagerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransactionCategorizer
{
    private $ruleChecker;
    private $entityManager;
    private $dispatcher;
    private $connectionKeeper;

    public function __construct(
        RuleChecker $ruleChecker,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        ConnectionKeeper $connectionKeeper
    ) {
        $this->ruleChecker = $ruleChecker;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->connectionKeeper = $connectionKeeper;
    }

    function categorizeOne(Transaction $transaction)
    {
        try {
            $newSubCategory = $this->ruleChecker->getMatchingSubCategory($transaction);
        } catch(TransactionMatchesMultipleRulesException $e) {
            $this->dispatcher->dispatch(
                new TransactionMatchesMultipleRulesEvent($e->getTransaction(), $e->getRules()),
                TransactionMatchesMultipleRulesEvent::NAME
            );

            $newSubCategory = null;
        }

        if ($newSubCategory !== null) {
            $oldSubCategory = $transaction->getSubCategory();
            $transaction->setSubCategory($newSubCategory);
            $this->entityManager->persist($transaction);

            if ($oldSubCategory === null) {
                $this->dispatcher->dispatch(
                    new TransactionCategorizedEvent($transaction),
                    TransactionCategorizedEvent::NAME
                );
            } else if ($oldSubCategory !== $newSubCategory) {
                $this->dispatcher->dispatch(
                    new TransactionCategoryChangedEvent($transaction, $oldSubCategory),
                    TransactionCategoryChangedEvent::NAME
                );
            }
        }
    }

    public function categorizeAllSync()
    {
        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findAllNotManuallyCategorized()
        ;

        foreach ($transactions as $transaction) {
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
        $this->connectionKeeper->keepAlive();
        $this->entityManager->clear();
        $this->ruleChecker->setRules();

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findAllNotManuallyCategorized()
        ;

        $this->categorizeInNextTick($loop, $transactions);
    }
}
