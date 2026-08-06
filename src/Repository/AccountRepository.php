<?php

namespace App\Repository;

use App\Entity\Account;
use App\Exception\AccountNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

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
        try {
            return $this->createQueryBuilder('a')
                ->andWhere('a.aliases LIKE :alias or a.name = :name')
                ->setParameter('alias', '%'.$search.'%')
                ->setParameter('name', $search)
                ->getQuery()
                ->getSingleResult()
            ;
        } catch (NoResultException $e) {
            throw new AccountNotFoundException($search);
        }
    }

    /**
     * @return Account Returns an Account object or null
     */
    public function findWithAliasExceptAccount($alias, $skipAccountId)
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $queryBuilder
            ->andWhere('a.aliases LIKE :alias')
            ->setParameter('alias', '%'.$alias.'%')
        ;

        if (null !== $skipAccountId) {
            $queryBuilder
                ->andWhere('a.id != :skipAccountId')
                ->setParameter('skipAccountId', $skipAccountId)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
