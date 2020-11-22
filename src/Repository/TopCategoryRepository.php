<?php

namespace App\Repository;

use App\Entity\TopCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TopCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TopCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TopCategory[]    findAll()
 * @method TopCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopCategory::class);
    }
}
