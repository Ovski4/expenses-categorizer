<?php

namespace App\Repository;

use App\Entity\SubCategoryTransactionRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubCategoryTransactionRule>
 *
 * @method SubCategoryTransactionRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubCategoryTransactionRule|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method SubCategoryTransactionRule[]    findAll()
 * @method SubCategoryTransactionRule[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class SubCategoryTransactionRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCategoryTransactionRule::class);
    }
}
