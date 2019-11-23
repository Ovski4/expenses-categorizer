<?php

namespace App\Repository;

use App\Entity\SubCategoryTransactionRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SubCategoryTransactionRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubCategoryTransactionRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubCategoryTransactionRule[]    findAll()
 * @method SubCategoryTransactionRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubCategoryTransactionRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategoryTransactionRule::class);
    }

    // /**
    //  * @return SubCategoryTransactionRule[] Returns an array of SubCategoryTransactionRule objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubCategoryTransactionRule
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
