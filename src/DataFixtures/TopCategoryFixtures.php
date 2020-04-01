<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\TopCategory;
use App\Entity\TransactionType;

class TopCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $topCategoryArray = [
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Advance'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Cash Withdrawal'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Communication'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Culture'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Drinks'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Tech'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Extras'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'For nothing'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Gifts'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Health'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'House'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Payback'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Personal care'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Transport'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Transfer'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Transfer'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Interest'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Payback'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Salary'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Rent']
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
