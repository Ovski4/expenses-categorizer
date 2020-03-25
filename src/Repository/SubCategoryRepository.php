<?php

namespace App\Repository;

use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SubCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubCategory[]    findAll()
 * @method SubCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategory::class);
    }

    /**
     * @return SubCategory[] Returns an array of SubCategory objects
     */
    public function findByTransactionType($value)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.topCategory', 't', 'WITH', 't.transactionType = ?1')
            ->orderBy('s.name', 'ASC')
            ->setParameter(1, $value)
            ->getQuery()
            ->getResult()
        ;
    }
}
