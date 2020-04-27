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
use Symfony\Contracts\Translation\TranslatorInterface;

class SubCategoryTransactionRuleFixtures extends Fixture implements DependentFixtureInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
                'subCategoryName' => $this->translator->trans('Rent'),
                'contains' => 'VIR SEPA PIERRE MEUNIER'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'RUNGIS MAGASIN U'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Highway toll'),
                'contains' => 'AUTOROUTE DU SUD'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Highway toll'),
                'contains' => 'PESSAC ATLANDES AUTOROU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Highway toll'),
                'contains' => 'COFIROUTE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Cash Withdrawal'),
                'contains' => 'RETRAIT DAB'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Internet subscription'),
                'contains' => 'PRLV SEPA SFR'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Tax'),
                'contains' => 'PRLV SEPA DIR. GENE. DES FINANCES PUBLIQUES ICS'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'LYDIA APP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Hosting'),
                'contains' => 'ROUBAIX KIMSUFI'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Hosting'),
                'contains' => 'ROUBAIX OVH'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Loan'),
                'contains' => 'ECH PRET CAP+IN 07228 203242 05'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Power (Electricity, gas...)'),
                'contains' => 'PRLV SEPA TOTAL DIRECT ENERGIE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Insurance'),
                'contains' => 'PRLV SEPA SECURIMUT'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Condominium fees'),
                'contains' => 'PRLV SEPA 115 RUE PROFESSEUR BE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Condominium fees'),
                'contains' => 'PRLV SEPA REGIE DE L\'OPERA'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Carpooling'),
                'contains' => 'BLABLACAR'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'BLABLACAR'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'VIR SEPA LORAINE FRIOUX'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer'),
                'contains' => 'VIR DE M BAPTISTE BOUCHEREAU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Transfer'),
                'contains' => 'VIR COMPTE COURANT JEUNE ACTIF'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'ANGERS CEDEX GEANT'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Gasoline'),
                'contains' => 'SAUGON ESSO SAUGON EST'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Restaurant'),
                'contains' => 'BAYONNE REST CHEZ BAI'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Restaurant'),
                'contains' => 'ANGERS LE TEMPLE DU CIE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Restaurant'),
                'contains' => 'ANGERS TEMPLE DU CIEL'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Restaurant'),
                'contains' => 'ST JEAN DE LU LE MAJESTIC'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'VIR GIEPS- SIN ET COM'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'VIR CPAM LOIRE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Restaurant'),
                'contains' => 'NANTES MAISON GRIMAUD'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Drinks'),
                'contains' => 'NANTES CLUB DE L ILE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Restaurant'),
                'contains' => 'REZE LA GOURMANDE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Doctor, medical centre'),
                'contains' => 'ANGERS DR SEGRETAIN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Video games'),
                'contains' => 'WWW.STEAMPOWERED'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Video games'),
                'contains' => 'STEAMGAMES.COM'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Pharmacy'),
                'contains' => 'ANGERS PHIE DE LORETTE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'ANGERS CARREFOUR EXPRES'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Transfer'),
                'contains' => 'VIR LIVRET BLEU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Train'),
                'contains' => 'SNCF OUIGO'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'SNCF OUIGO'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Rent'),
                'contains' => 'VIR MR OU MME LE TRAOU DOMIN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'BIOCOOP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Gasoline'),
                'contains' => 'ANGERS DAC TIMAEL'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Fast food'),
                'contains' => 'BURGER KING'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Sport field'),
                'contains' => 'LES PONTS DE CLIMB UP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Bank fee'),
                'contains' => 'F COTIS EUC JEUNE ACTIF'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Rent'),
                'contains' => 'VIR MLLE NATHALIE ROCHE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Sport field'),
                'contains' => 'NANTES SMASH GOAL'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Bakery'),
                'contains' => 'NANTES AU PAIN GOURMAND'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Bakery'),
                'contains' => 'SAINT JULIEN LES SAVEURS D AN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Train'),
                'contains' => 'PARIS TRAINLINE'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'PARIS TRAINLINE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Tramway or subway'),
                'contains' => 'PARIS IRIGO'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Salary'),
                'contains' => 'VIR SARL IDCI CONSULTING'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Bakery'),
                'contains' => 'ANGERS MAISON LATAIRE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Train'),
                'contains' => 'THETRAINLINE.COM'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Music'),
                'contains' => 'LONDON GOOGLE GOOGLE',
                'amount' => -9.99,
                'operator' => Operator::EQUALS,
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Hotel, Airbnb...'),
                'contains' => 'AIRBNB'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Advance'),
                'contains' => 'PAIEMENT CB 1801 PAYLI2441535/ AMAZON PAYMENTS CARTE 60839786',
                'amount' => -27.34,
                'operator' => Operator::EQUALS,
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Night club'),
                'contains' => 'PAIEMENT CB 1901 LYON YURPLAN CARTE 60839786',
                'amount' => -22.99,
                'operator' => Operator::EQUALS,
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Insurance'),
                'contains' => 'PARIS ACS'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Hotel, Airbnb...'),
                'contains' => 'ST JEAN DE MO LE SLOI'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Sport field'),
                'contains' => 'ST HERBLAIN LE SPORTING CLU'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Payback'),
                'contains' => 'STE FINANCIERE DU PORTE'
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Interest'),
                'contains' => 'INTERETS'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'REZE CENTRE LECLERC'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Car park'),
                'contains' => 'ANGERS SORTIE MITTERRAN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Flight'),
                'contains' => 'EASYJET'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Pharmacy'),
                'contains' => 'FONTENAY LE C PHCIE PRINCIPAL'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Insurance'),
                'contains' => 'ANDREZIEUX BO MACIF RHONES ALP'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Donation'),
                'contains' => 'KISSKISSBANK'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Tramway or subway'),
                'contains' => 'PARIS 14 SNCF',
                'amount' => -16.9,
                'operator' => Operator::EQUALS,
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Stamps'),
                'contains' => 'PARIS 14 LA POSTE BOUTIQU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Bakery'),
                'contains' => 'ANGERS GRENIER A PAIN'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Sport field'),
                'contains' => 'REZE BADMIN SQUASH'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Car park'),
                'contains' => 'NANTES STATION VOIRIE'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'ST JULIEN DE CARREFOUR MARKET'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Supermarket and groceries'),
                'contains' => 'NANTES SPAR'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Drinks'),
                'contains' => 'BAZOGES EN PA STABU'
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Drinks'),
                'contains' => 'ANTIGNY LE BAR MITON'
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
