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
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function exists(Transaction $transaction): bool
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

    /**
     * @return Transaction[]
     */
    public function findByTopCategory(TopCategory $topCategory): array
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

    /**
     * @return Transaction[]
     */
    public function findAllNotManuallyCategorized(): array
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
        $sum = $this->createQueryBuilder('t')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->select('SUM(t.amount) as amount_sum')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (float) ($sum ?? 0);
    }

    public function getBalanceByTag(Tag $tag): float
    {
        $queryBUilder = $this->createQueryBuilder('t');

        $sum = $queryBUilder->innerJoin('t.tags', 'tags', Join::WITH, $queryBUilder->expr()->eq('tags.id', ':tag'))
            ->setParameter('tag', $tag->getId())
            ->select('SUM(t.amount) as amount_sum')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (float) ($sum ?? 0);
    }
}
