<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @return Account Returns an Account object
     */
    public function findByAliasOrName($search)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.aliases LIKE :alias or a.name = :name')
            ->setParameter('alias', '%'.$search.'%')
            ->setParameter('name', $search)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    /**
     * @return Account Returns an Account object or null
     */
    public function findWithAliasExceptAccount($alias, $skipAccountId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.aliases LIKE :alias')
            ->andWhere('a.id != :skipAccountId')
            ->setParameter('alias', '%'.$alias.'%')
            ->setParameter('skipAccountId', $skipAccountId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
