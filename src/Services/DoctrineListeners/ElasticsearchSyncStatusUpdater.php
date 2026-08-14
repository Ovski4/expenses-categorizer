<?php

namespace App\Services\DoctrineListeners;

use App\Entity\Account;
use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Event\OnFlushEventArgs;

class ElasticsearchSyncStatusUpdater
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $transactions = [];

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            // For some reason the ramsey uuid ends up in the changeset since upgrading to symfony 6. Ignore it.
            $changes = $unitOfWork->getEntityChangeSet($entity);
            if (1 === count($changes) && isset($changes['id'])) {
                continue;
            }

            if ($entity instanceof SubCategory) {
                $transactions = $this->transactionRepository->findBy(['subCategory' => $entity->getId()]);
            }

            if ($entity instanceof Account) {
                $transactions = $this->transactionRepository->findBy(['account' => $entity->getId()]);
            }

            if ($entity instanceof TopCategory) {
                $transactions = $this->transactionRepository->findByTopCategory($entity);
            }

            if ($entity instanceof Transaction) {
                if (!(
                    isset($changes['toSyncInElasticsearch'])
                    && false === $changes['toSyncInElasticsearch'][1]
                )) {
                    $transactions[] = $entity;
                }
            }

            foreach ($transactions as $transaction) {
                $transaction->setToSyncInElasticsearch(true);
                $objectManager->persist($transaction);
                $classMetadata = $objectManager->getClassMetadata(Transaction::class);

                if ($unitOfWork->getEntityChangeSet($entity)) {
                    $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $transaction);
                } else {
                    $unitOfWork->computeChangeSet($classMetadata, $transaction);
                }
            }
        }
    }
}
