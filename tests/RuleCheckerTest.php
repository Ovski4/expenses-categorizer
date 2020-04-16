<?php

use App\Entity\Operator;
use App\Services\RuleChecker;
use PHPUnit\Framework\TestCase;
use App\Entity\SubCategory;
use App\Entity\Transaction;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\TopCategory;
use App\Entity\TransactionType;
use App\Repository\SubCategoryTransactionRuleRepository;

class RuleCheckerTest extends TestCase
{
    private $ruleRepository;

    private function createTopCategory()
    {
        $topCategory = new TopCategory();
        $topCategory
            ->setName('Test top category expense')
            ->setTransactionType(TransactionType::EXPENSES)
        ;

        return $topCategory;
    }

    private function createSubCategory()
    {
        $subCategory = new SubCategory();
        $subCategory
            ->setName('Test sub category expense 1')
            ->setTopCategory($this->createTopCategory())
        ;

        return $subCategory;
    }

    private function createTransaction($label, $amount)
    {
        $transaction = new Transaction();
        $transaction
            ->setAccount('Fake account')
            ->setAmount($amount)
            ->setCreatedAt(new \DateTime('now'))
            ->setLabel($label)
        ;

        return $transaction;
    }

    private function mockSubCategoryTransactionRuleRepository($rules)
    {
        $this->ruleRepository = $this->createMock(SubCategoryTransactionRuleRepository::class);
        $this->ruleRepository->expects($this->any())
            ->method('findAll')
            ->willReturn($rules)
        ;
    }

    public function testRuleIsChecked()
    {
        $rule = new SubCategoryTransactionRule();
        $rule
            ->setContains('dummy text')
            ->setSubCategory($this->createSubCategory())
        ;

        $this->mockSubCategoryTransactionRuleRepository([$rule]);
        $ruleChecker = new RuleChecker($this->ruleRepository);
        $transaction1 = $this->createTransaction('Some dummy text here', -22);
        $transaction2 = $this->createTransaction('Some even dummiest text here', -22);

        $this->assertEquals($ruleChecker->getMatchingSubCategory($transaction1), $this->createSubCategory());
        $this->assertNull($ruleChecker->getMatchingSubCategory($transaction2));
    }

    public function testRuleWithAmountIsNotChecked()
    {
        $rule = new SubCategoryTransactionRule();
        $rule
            ->setContains('dummy text')
            ->setSubCategory($this->createSubCategory())
            ->setAmount(-23)
            ->setOperator(Operator::EQUALS)
        ;

        $this->mockSubCategoryTransactionRuleRepository([$rule]);
        $ruleChecker = new RuleChecker($this->ruleRepository);
        $transaction1 = $this->createTransaction('Some dummy text here', -22);

        $this->assertNull($ruleChecker->getMatchingSubCategory($transaction1), $this->createSubCategory());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Multiple sub categories found for transaction
     */
    public function testExceptionIsThrown()
    {
        $rule1 = new SubCategoryTransactionRule();
        $rule1
            ->setContains('dummy text')
            ->setSubCategory($this->createSubCategory())
        ;
        $rule2 = new SubCategoryTransactionRule();
        $rule2
            ->setContains('some text')
            ->setSubCategory($this->createSubCategory())
        ;

        $this->mockSubCategoryTransactionRuleRepository([$rule1, $rule2]);
        $ruleChecker = new RuleChecker($this->ruleRepository);
        $transaction = $this->createTransaction('Here is some text and dummy text here', -22);
        $ruleChecker->getMatchingSubCategory($transaction);
    }
}