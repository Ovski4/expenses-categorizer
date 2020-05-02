<?php

namespace App\DataFixtures;

use App\Entity\SubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use Symfony\Contracts\Translation\TranslatorInterface;

class InitialTransactionsFixtures extends Fixture implements DependentFixtureInterface
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
        $transactions = [
            [
                'label' => 'SOLDE CREDITEUR AU 30/04/2014',
                'createdAt' => new \DateTime('2014-04-30'),
                'account' => 'C/C EUROCOMPTE JEUNE N° 00020324201',
                'amount' => 591.73,
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'SOLDE CREDITEUR AU 01/01/2014',
                'createdAt' => new \DateTime('2014-01-01'),
                'account' => 'LIVRET JEUNE N° 00020324202',
                'amount' => 1751.70,
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'SOLDE CREDITEUR AU 01/05/2014',
                'createdAt' => new \DateTime('2014-05-01'),
                'account' => 'LIVRET BLEU N° 00020324203',
                'amount' => 6181.66,
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'SOLDE CREDITEUR AU 01/05/2014',
                'createdAt' => new \DateTime('2014-01-01'),
                'account' => 'COMPTE EPARGNE LOGEMENT N° 00020324204',
                'amount' => 2000,
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'CLOTURE DE COMPTE AU 28/02/2017',
                'createdAt' => new \DateTime('2017-02-28'),
                'account' => 'C/C EUROCOMPTE JEUNE N° 00020324201',
                'amount' => -2766.89,
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'SOLDE CREDITEUR AU 28/02/2017',
                'createdAt' => new \DateTime('2017-02-28'),
                'account' => 'Compte Courant JEUNE ACTIF N° 00020324201',
                'amount' => 2766.89,
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'CLOTURE DE COMPTE AU 02/03/2020',
                'createdAt' => new \DateTime('2020-03-02'),
                'account' => 'Compte Courant JEUNE ACTIF N° 00020324201',
                'amount' => -1860.24,
                'transactionType' => TransactionType::EXPENSES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'label' => 'SOLDE CREDITEUR AU 02/03/2020',
                'createdAt' => new \DateTime('2020-03-02'),
                'account' => 'C/C EUROCOMPTE CONFORT N° 00020324201',
                'amount' => 1860.24,
                'transactionType' => TransactionType::REVENUES,
                'subCategoryName' => $this->translator->trans('Transfer')
            ]
        ];

        // foreach ($transactions as $item) {
        //     $transaction = new Transaction();
        //     $transaction
        //         ->setLabel($item['label'])
        //         ->setCreatedAt($item['createdAt'])
        //         ->setAccount($item['account'])
        //         ->setAmount($item['amount'])
        //     ;

        //     $subCategory = $manager
        //         ->getRepository(SubCategory::class)
        //         ->findByNameAndTransactionType($item['subCategoryName'], $item['transactionType'])
        //     ;
        //     $transaction->setSubCategory($subCategory);
        //     $manager->persist($transaction);
        // }

        // $manager->flush();
    }
}
