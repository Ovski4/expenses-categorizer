<?php

namespace App\Services\DoctrineListeners;

use App\Entity\Account;
use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\Transaction;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

class ElasticsearchSyncStatusUpdater implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $transactions = [];

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {

            if ($entity instanceof SubCategory) {
                $transactions = $entityManager
                    ->getRepository(Transaction::class)
                    ->findBy(['subCategory' => $entity->getId()])
                ;
            }

            if ($entity instanceof Account) {
                $transactions = $entityManager
                    ->getRepository(Transaction::class)
                    ->findBy(['account' => $entity->getId()])
                ;
            }

            if ($entity instanceof TopCategory) {
                $transactions = $entityManager
                    ->getRepository(Transaction::class)
                    ->findByTopCategory($entity)
                ;
            }

            if ($entity instanceof Transaction) {
                $changes = $unitOfWork->getEntityChangeSet($entity);

                if (!(
                    isset($changes['toSyncInElasticsearch']) &&
                    $changes['toSyncInElasticsearch'][1] === false
                )) {
                    $transactions[] = $entity;
                }
            }

            foreach ($transactions as $transaction) {
                $transaction->setToSyncInElasticsearch(true);
                $entityManager->persist($transaction);
                $classMetadata = $entityManager->getClassMetadata(Transaction::class);
                $unitOfWork->computeChangeSet($classMetadata, $transaction);
            }
        }
    }
}
