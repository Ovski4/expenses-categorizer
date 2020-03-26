<?php

namespace App\DataFixtures;

use App\Entity\SubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\TransactionType;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SubCategoryTransactionRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return array(
            SubCategoryFixtures::class,
        );
    }

    public function load(ObjectManager $manager)
    {
        $subCategoryTransactionRuleArray = [
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Rent',
                'contains' => 'VIR SEPA PIERRE MEUNIER'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Supermarket and groceries',
                'contains' => 'RUNGIS MAGASIN U'
            ]
        ];

        foreach ($subCategoryTransactionRuleArray as $item) {
            $subCategoryTransactionRule = new SubCategoryTransactionRule();
            $subCategoryTransactionRule->setContains($item['contains']);

            $subCategory = $manager
                ->getRepository(SubCategory::class)
                ->findByNameAndTransactionType($item['subCategoryName'], $item['transactionType'])
            ;
            $subCategoryTransactionRule->setSubCategory($subCategory);
            $manager->persist($subCategoryTransactionRule);
        }

        $manager->flush();
    }
}
