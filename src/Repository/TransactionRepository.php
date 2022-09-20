<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Tag;
use App\Entity\TopCategory;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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

    public function exists(Transaction $transaction)
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.label = :label')
            ->andWhere('t.createdAt = :createdAt')
            ->andWhere('t.amount = :amount')
            ->setParameter('account', $transaction->getAccount())
            ->setParameter('label', $transaction->getLabel())
            ->setParameter('amount', $transaction->getAmount())
            ->setParameter('createdAt', $transaction->getCreatedAt()->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;

        return empty($result) ? false : true;
    }

    public function findByTopCategory(TopCategory $topCategory)
    {
        $results = $this->createQueryBuilder('t')
            ->join('t.subCategory', 'sc')
            ->where('sc.topCategory = :topCategory')
            ->setParameter('topCategory', $topCategory)
            ->getQuery()
            ->getResult()
        ;

        return $results;
    }

    public function findAllNotManuallyCategorized()
    {
        $results = $this->createQueryBuilder('t')
            ->where('t.categorizedManually = :categorizedManually')
            ->setParameter('categorizedManually', false)
            ->getQuery()
            ->getResult()
        ;

        return $results;
    }

    public function getBalanceByAccount(Account $account): float
    {
        return $this->createQueryBuilder('t')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->select('SUM(t.amount) as amount_sum')
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;
    }

    public function getBalanceByTag(Tag $tag): float
    {
        $queryBUilder = $this->createQueryBuilder('t');

        return $queryBUilder->innerJoin('t.tags', 'tags', Join::WITH, $queryBUilder->expr()->eq('tags.id', ':tag'))
            ->setParameter('tag', $tag->getId())
            ->select('SUM(t.amount) as amount_sum')
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;
    }
}
