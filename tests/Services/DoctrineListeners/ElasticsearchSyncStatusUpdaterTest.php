<?php

namespace App\Tests\Services\DoctrineListeners;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ElasticsearchSyncStatusUpdaterTest extends KernelTestCase
{
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testUpdatingATransactionFlagsItForSync(): void
    {
        $transaction = $this->createSyncedTransaction();

        $transaction->setLabel('OVH SAS renamed');
        $this->manager->flush();

        $this->assertSyncStatus(true, $transaction);
    }

    public function testMarkingATransactionAsSyncedIsNotUndone(): void
    {
        // A transaction the listener has flagged, waiting to be synced
        $transaction = $this->createSyncedTransaction();
        $transaction->setLabel('OVH SAS renamed');
        $this->manager->flush();
        $this->assertSyncStatus(true, $transaction);

        // The sync is done: the listener must not flag it again
        $transaction->setToSyncInElasticsearch(false);
        $this->manager->flush();

        $this->assertSyncStatus(false, $transaction);
    }

    /**
     * A transaction already synced in elasticsearch. Inserts are not watched by
     * the listener, so the flag stays false until the transaction is updated.
     */
    private function createSyncedTransaction(): Transaction
    {
        $account = (new Account())
            ->setName(uniqid('Account '))
            ->setCurrency('EUR')
        ;
        $this->manager->persist($account);

        $transaction = (new Transaction())
            ->setLabel('OVH SAS')
            ->setAmount(-34.3)
            ->setCreatedAt(new \DateTime('2025-04-02 00:00:00'))
            ->setAccount($account)
            ->setToSyncInElasticsearch(false)
        ;
        $this->manager->persist($transaction);
        $this->manager->flush();

        $this->assertSyncStatus(false, $transaction);

        return $transaction;
    }

    /**
     * Check the entity in memory, then read it back from the database, to tell
     * a change the listener only made in memory from one it actually wrote.
     */
    private function assertSyncStatus(bool $expected, Transaction $transaction): void
    {
        $this->assertSame(
            $expected,
            $transaction->getToSyncInElasticsearch(),
            'The in memory transaction has the wrong sync status'
        );

        $this->manager->refresh($transaction);

        $this->assertSame(
            $expected,
            $transaction->getToSyncInElasticsearch(),
            'The stored transaction has the wrong sync status'
        );
    }
}
