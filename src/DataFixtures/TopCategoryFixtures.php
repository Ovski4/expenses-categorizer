<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\TopCategory;
use App\Entity\TransactionType;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopCategoryFixtures extends Fixture
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function load(ObjectManager $manager)
    {
        $topCategoryArray = [
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Advance')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Cash Withdrawal')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Clothes')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Communication')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Culture')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Drinks')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Tech')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Extras')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('For nothing')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Gifts')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Health')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('House')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Payback')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Personal care')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Transfer')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Transfer')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Interest')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Payback')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Salary')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Rent')
            ]
        ];

        foreach ($topCategoryArray as $topCategoryItem) {
            $topCategory = new TopCategory();
            $topCategory->setName($topCategoryItem['name']);
            $topCategory->setTransactionType($topCategoryItem['transactionType']);
            $manager->persist($topCategory);
        }

        $manager->flush();
    }
}
