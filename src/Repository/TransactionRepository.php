<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findUncategorizedTransactions()
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.subCategory is NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function exists(Transaction $transaction)
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.label = :label')
            ->andWhere('t.created_at = :created_at')
            ->andWhere('t.amount = :amount')
            ->setParameter('account', $transaction->getAccount())
            ->setParameter('label', $transaction->getLabel())
            ->setParameter('amount', $transaction->getAmount())
            ->setParameter('created_at', $transaction->getCreatedAt()->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;

        return empty($result) ? false : true;
    }
}
