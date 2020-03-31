<?php

namespace App\DataFixtures;

use App\Entity\Operator;
use App\Entity\SubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\TransactionType;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\NoResultException;

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
                'subCategoryName' => 'Hosting',
                'contains' => 'ROUBAIX OVH'
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
                'subCategoryName' => 'Condominium fees',
                'contains' => 'PRLV SEPA REGIE DE L\'OPERA'
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
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Payback',
                'contains' => 'VIR SEPA LORAINE FRIOUX'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Transfer',
                'contains' => 'VIR DE M BAPTISTE BOUCHEREAU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Transfer',
                'contains' => 'VIR COMPTE COURANT JEUNE ACTIF'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Supermarket and groceries',
                'contains' => 'ANGERS CEDEX GEANT'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Gasoline',
                'contains' => 'SAUGON ESSO SAUGON EST'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Restaurant',
                'contains' => 'BAYONNE REST CHEZ BAI'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Restaurant',
                'contains' => 'ANGERS LE TEMPLE DU CIE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Restaurant',
                'contains' => 'ST JEAN DE LU LE MAJESTIC'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Payback',
                'contains' => 'VIR GIEPS- SIN ET COM'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Payback',
                'contains' => 'VIR CPAM LOIRE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Restaurant',
                'contains' => 'NANTES MAISON GRIMAUD'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Drinks',
                'contains' => 'NANTES CLUB DE L ILE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Restaurant',
                'contains' => 'REZE LA GOURMANDE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Doctor, medical centre',
                'contains' => 'ANGERS DR SEGRETAIN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Video games',
                'contains' => 'WWW.STEAMPOWERED'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Pharmacy',
                'contains' => 'ANGERS PHIE DE LORETTE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Supermarket and groceries',
                'contains' => 'ANGERS CARREFOUR EXPRES'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Transfer',
                'contains' => 'VIR LIVRET BLEU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Train',
                'contains' => 'SNCF OUIGO'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Rent',
                'contains' => 'VIR MR OU MME LE TRAOU DOMIN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Supermarket and groceries',
                'contains' => 'BIOCOOP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Gasoline',
                'contains' => 'ANGERS DAC TIMAEL'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Fast food',
                'contains' => 'BURGER KING'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Sport field',
                'contains' => 'LES PONTS DE CLIMB UP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Bank fee',
                'contains' => 'F COTIS EUC JEUNE ACTIF'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Rent',
                'contains' => 'VIR MLLE NATHALIE ROCHE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Sport field',
                'contains' => 'NANTES SMASH GOAL'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Bakery',
                'contains' => 'NANTES AU PAIN GOURMAND'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Bakery',
                'contains' => 'SAINT JULIEN LES SAVEURS D AN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Train',
                'contains' => 'PARIS TRAINLINE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Tramway or subway',
                'contains' => 'PARIS IRIGO'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => 'Salary',
                'contains' => 'VIR SARL IDCI CONSULTING'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Bakery',
                'contains' => 'ANGERS MAISON LATAIRE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Train',
                'contains' => 'THETRAINLINE.COM'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => 'Music',
                'contains' => 'LONDON GOOGLE GOOGLE',
                'amount' => -9.99,
                'operator' => Operator::EQUALS,
            ]
        ];

        foreach ($subCategoryTransactionRuleArray as $item) {
            $subCategoryTransactionRule = new SubCategoryTransactionRule();
            $subCategoryTransactionRule->setContains($item['contains']);
            if (isset($item['amount'])) {
                $subCategoryTransactionRule
                    ->setAmount($item['amount'])
                    ->setOperator($item['operator'])
                ;
            }

            try {
                $subCategory = $manager
                    ->getRepository(SubCategory::class)
                    ->findByNameAndTransactionType($item['subCategoryName'], $item['transactionType'])
                ;
            } catch (NoResultException $e) {
                throw new \Exception(sprintf('No subCategory found with name %s', $item['subCategoryName']));
            }

            $subCategoryTransactionRule->setSubCategory($subCategory);
            $manager->persist($subCategoryTransactionRule);
        }

        $manager->flush();
    }
}
