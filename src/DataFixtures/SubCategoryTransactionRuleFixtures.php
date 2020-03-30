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
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Highway toll',
                'contains' => 'AUTOROUTE DU SUD'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Highway toll',
                'contains' => 'PESSAC ATLANDES AUTOROU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Highway toll',
                'contains' => 'COFIROUTE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Cash Withdrawal',
                'contains' => 'RETRAIT DAB'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Internet subscription',
                'contains' => 'PRLV SEPA SFR'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Tax',
                'contains' => 'PRLV SEPA DIR. GENE. DES FINANCES PUBLIQUES ICS'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Payback',
                'contains' => 'LYDIA APP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Hosting',
                'contains' => 'ROUBAIX KIMSUFI'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Loan',
                'contains' => 'ECH PRET CAP+IN 07228 203242 05'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Power (Electricity, gas...)',
                'contains' => 'PRLV SEPA TOTAL DIRECT ENERGIE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Insurance',
                'contains' => 'PRLV SEPA SECURIMUT'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Condominium fees',
                'contains' => 'PRLV SEPA 115 RUE PROFESSEUR BE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Carpooling',
                'contains' => 'BLABLACAR'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Payback',
                'contains' => 'BLABLACAR'
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
