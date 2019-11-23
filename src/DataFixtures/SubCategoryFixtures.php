<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\TransactionType;

class SubCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return array(
            TopCategoryFixtures::class,
        );
    }

    public function load(ObjectManager $manager)
    {
        $subCategories = [
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Cash Withdrawal', 'topCategoryName' => 'Cash Withdrawal'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Bond', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Camping booking', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Furnitures', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Hotel, Airbnb...', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Loan', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Power (Electricity, gas...)', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Rent', 'topCategoryName' => 'Accommodation'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Bicycle equipment', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Bicycle repair', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Camping equipment', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Cryptocurrency', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Kayaking', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Museum', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Night club', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Other activities', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Pool', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Sport extreme', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Sport event', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Sport field', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Sport gear', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Tour', 'topCategoryName' => 'Activities'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Clothes', 'topCategoryName' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Shoe laces', 'topCategoryName' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Shoes', 'topCategoryName' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Short', 'topCategoryName' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Sun glasses', 'topCategoryName' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'T-shirt', 'topCategoryName' => 'Clothes'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Internet subscription', 'topCategoryName' => 'Communication'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Mobile subscription', 'topCategoryName' => 'Communication'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Android app', 'topCategoryName' => 'Culture'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Book', 'topCategoryName' => 'Culture'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Music', 'topCategoryName' => 'Culture'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Video games', 'topCategoryName' => 'Culture'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Beer', 'topCategoryName' => 'Drinks'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Cocktail', 'topCategoryName' => 'Drinks'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Coffee', 'topCategoryName' => 'Drinks'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Liquor', 'topCategoryName' => 'Drinks'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Wine', 'topCategoryName' => 'Drinks'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Computer accessory', 'topCategoryName' => 'Tech'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'GoPro accessory', 'topCategoryName' => 'Tech'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Head phones', 'topCategoryName' => 'Tech'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Hosting', 'topCategoryName' => 'Tech'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Comfort', 'topCategoryName' => 'Extras'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Leather goods', 'topCategoryName' => 'Extras'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Art decoration', 'topCategoryName' => 'Extras'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Bank fee', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Condominium fees', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Post office fees', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Property tax', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Fees', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Insurance', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Stamps', 'topCategoryName' => 'Fees'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Bakery', 'topCategoryName' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Fast food', 'topCategoryName' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Ice creams', 'topCategoryName' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Restaurant', 'topCategoryName' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Supermarket and groceries', 'topCategoryName' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Outdoor Market', 'topCategoryName' => 'Food'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'For nothing', 'topCategoryName' => 'For nothing'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Gifts', 'topCategoryName' => 'Gifts'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Doctor, medical centre', 'topCategoryName' => 'Health'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Pharmacy', 'topCategoryName' => 'Health'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'House equipment', 'topCategoryName' => 'House'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Hairdresser', 'topCategoryName' => 'Personal care'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Laundry', 'topCategoryName' => 'Personal care'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Shampoo', 'topCategoryName' => 'Personal care'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Tissues', 'topCategoryName' => 'Personal care'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Train', 'topCategoryName' => 'Transport'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Bus', 'topCategoryName' => 'Transport'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Ferry', 'topCategoryName' => 'Transport'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Flight', 'topCategoryName' => 'Transport'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Gasoline', 'topCategoryName' => 'Transport'],
            ['transactionType' => TransactionType::EXPENSES, 'name' => 'Uber', 'topCategoryName' => 'Transport'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'External transfer', 'topCategoryName' => 'External transfer'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Interest', 'topCategoryName' => 'Interest'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Payback', 'topCategoryName' => 'Payback'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Salary', 'topCategoryName' => 'Salary'],
            ['transactionType' => TransactionType::REVENUES, 'name' => 'Rent', 'topCategoryName' => 'Rent']
        ];

        foreach ($subCategories as $subCategoryItem) {
            $subCategory = new SubCategory();
            $topCategory = $manager
                ->getRepository(TopCategory::class)
                ->findOneBy([
                    'name' => $subCategoryItem['topCategoryName'],
                    'transaction_type' => $subCategoryItem['transactionType']
                ])
            ;
            $subCategory
                ->setName($subCategoryItem['name'])
                ->setTransactionType($subCategoryItem['transactionType'])
                ->setTopCategory($topCategory)
            ;
            $manager->persist($subCategory);
        }

        $manager->flush();
    }
}
