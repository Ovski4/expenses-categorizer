<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\TransactionType;
use Symfony\Contracts\Translation\TranslatorInterface;

class SubCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getDependencies()
    {
        return array(
            TopCategoryFixtures::class,
        );
    }

    public function load(ObjectManager $manager)
    {
        $subCategories = [
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Advance'),
                'topCategoryName' => $this->translator->trans('Advance')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Cash Withdrawal'),
                'topCategoryName' => $this->translator->trans('Cash Withdrawal')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Bond'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Camping booking'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Furnitures'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Hotel, Airbnb...'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Loan'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Power (Electricity, gas...)'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Rent'),
                'topCategoryName' => $this->translator->trans('Accommodation')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Bicycle equipment'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Bicycle repair'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Camping equipment'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Cryptocurrency'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Kayaking'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Museum'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Night club'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Other activities'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Pool'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Sport extreme'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Sport event'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Sport field'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Sport gear'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Tour'),
                'topCategoryName' => $this->translator->trans('Activities')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Clothes'),
                'topCategoryName' => $this->translator->trans('Clothes')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Internet subscription'),
                'topCategoryName' => $this->translator->trans('Communication')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Mobile subscription'),
                'topCategoryName' => $this->translator->trans('Communication')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Games'),
                'topCategoryName' => $this->translator->trans('Culture')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Android app'),
                'topCategoryName' => $this->translator->trans('Culture')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Book'),
                'topCategoryName' => $this->translator->trans('Culture')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Music'),
                'topCategoryName' => $this->translator->trans('Culture')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Video games'),
                'topCategoryName' => $this->translator->trans('Culture')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Drinks'),
                'topCategoryName' => $this->translator->trans('Drinks')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Tech accessory'),
                'topCategoryName' => $this->translator->trans('Tech')],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Hosting'),
                'topCategoryName' => $this->translator->trans('Tech')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Comfort'),
                'topCategoryName' => $this->translator->trans('Extras')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Leather goods'),
                'topCategoryName' => $this->translator->trans('Extras')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Art decoration'),
                'topCategoryName' => $this->translator->trans('Extras')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Fine'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Bank fee'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Condominium fees'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Post office fees'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Property tax'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Tax'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Fees'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Insurance'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Stamps'),
                'topCategoryName' => $this->translator->trans('Fees')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Bakery'),
                'topCategoryName' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Fast food'),
                'topCategoryName' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Ice creams'),
                'topCategoryName' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Restaurant'),
                'topCategoryName' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Supermarket and groceries'),
                'topCategoryName' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Outdoor Market'),
                'topCategoryName' => $this->translator->trans('Food')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('For nothing'),
                'topCategoryName' => $this->translator->trans('For nothing')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Gifts'),
                'topCategoryName' => $this->translator->trans('Gifts')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Donation'),
                'topCategoryName' => $this->translator->trans('Gifts')],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Doctor, medical centre'),
                'topCategoryName' => $this->translator->trans('Health')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Pharmacy'),
                'topCategoryName' => $this->translator->trans('Health')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('House equipment'),
                'topCategoryName' => $this->translator->trans('House')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Plumbing'),
                'topCategoryName' => $this->translator->trans('House')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Payback'),
                'topCategoryName' => $this->translator->trans('Payback')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Hairdresser'),
                'topCategoryName' => $this->translator->trans('Personal care')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Laundry'),
                'topCategoryName' => $this->translator->trans('Personal care')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Shampoo'),
                'topCategoryName' => $this->translator->trans('Personal care')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Tissues'),
                'topCategoryName' => $this->translator->trans('Personal care')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Train'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Tramway or subway'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Bus'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Ferry'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Flight'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Gasoline'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Uber'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Carpooling'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Car park'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Highway toll'),
                'topCategoryName' => $this->translator->trans('Transport')
            ],
            [
                'transactionType' => TransactionType::EXPENSES,
                'name' => $this->translator->trans('Transfer'),
                'topCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Transfer'),
                'topCategoryName' => $this->translator->trans('Transfer')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Interest'),
                'topCategoryName' => $this->translator->trans('Interest')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Payback'),
                'topCategoryName' => $this->translator->trans('Payback')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Salary'),
                'topCategoryName' => $this->translator->trans('Salary')
            ],
            [
                'transactionType' => TransactionType::REVENUES,
                'name' => $this->translator->trans('Rent'),
                'topCategoryName' => $this->translator->trans('Rent')
            ]
        ];

        foreach ($subCategories as $subCategoryItem) {
            $subCategory = new SubCategory();
            $topCategory = $manager
                ->getRepository(TopCategory::class)
                ->findOneBy([
                    'name' => $subCategoryItem['topCategoryName'],
                    'transactionType' => $subCategoryItem['transactionType']
                ])
            ;
            $subCategory
                ->setName($subCategoryItem['name'])
                ->setTopCategory($topCategory)
            ;
            $manager->persist($subCategory);
        }

        $manager->flush();
    }
}
