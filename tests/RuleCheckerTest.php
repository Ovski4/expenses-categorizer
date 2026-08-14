<?php

use App\Entity\Account;
use App\Entity\Operator;
use App\Entity\SubCategory;
use App\Entity\SubCategoryTransactionRule;
use App\Entity\TopCategory;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Repository\SubCategoryTransactionRuleRepository;
use App\Services\RuleChecker;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class RuleCheckerTest extends TestCase
{
    private SubCategoryTransactionRuleRepository $ruleRepository;

    private function createTopCategory(): TopCategory
    {
        $topCategory = new TopCategory();
        $topCategory
            ->setName('Test top category expense')
            ->setTransactionType(TransactionType::EXPENSES)
        ;

        return $topCategory;
    }

    private function createSubCategory(string $name = 'Test sub category expense 1'): SubCategory
    {
        $subCategory = new SubCategory();
        $subCategory
            ->setName($name)
            ->setTopCategory($this->createTopCategory())
        ;

        return $subCategory;
    }

    private function createTransaction(string $label, float $amount): Transaction
    {
        $transaction = new Transaction();
        $transaction
            ->setAccount((new Account())->setName('Fake account')->setCurrency('EUR'))
            ->setAmount($amount)
            ->setCreatedAt(new DateTime('now'))
            ->setLabel($label)
        ;

        return $transaction;
    }

    /**
     * @param SubCategoryTransactionRule[] $rules
     */
    private function mockSubCategoryTransactionRuleRepository(array $rules): void
    {
        $this->ruleRepository = $this->createMock(SubCategoryTransactionRuleRepository::class);
        $this->ruleRepository->expects($this->any())
            ->method('findAll')
            ->willReturn($rules)
        ;
    }

    public function testRuleIsChecked(): void
    {
        $rule = new SubCategoryTransactionRule();
        $rule
            ->setContains('dummy text')
            ->setSubCategory($this->createSubCategory())
        ;

        $this->mockSubCategoryTransactionRuleRepository([$rule]);
        $ruleChecker = new RuleChecker($this->ruleRepository);
        $transaction1 = $this->createTransaction('Some dummy TEXT here', -22);
        $transaction2 = $this->createTransaction('Some even dummier text here', -22);

        $this->assertEquals($ruleChecker->getMatchingSubCategory($transaction1), $this->createSubCategory());
        $this->assertNull($ruleChecker->getMatchingSubCategory($transaction2));
    }

    public function testRuleWithAmountIsNotChecked(): void
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

    public function testRuleWithAmountIsChecked(): void
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
        $transaction1 = $this->createTransaction('Some dummy text here', -23);

        $this->assertNotNull($ruleChecker->getMatchingSubCategory($transaction1), $this->createSubCategory());
    }

    /**
     * Multiple matches with different categories.
     */
    public function testExceptionIsThrown(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Multiple rules are matching the transaction');
        $rule1 = new SubCategoryTransactionRule();
        $rule1
            ->setContains('dummy text')
            ->setSubCategory($this->createSubCategory())
        ;
        $rule2 = new SubCategoryTransactionRule();
        $rule2
            ->setContains('some text')
            ->setSubCategory($this->createSubCategory('Test sub category expense 2'))
        ;

        $this->mockSubCategoryTransactionRuleRepository([$rule1, $rule2]);
        $ruleChecker = new RuleChecker($this->ruleRepository);
        $transaction = $this->createTransaction('Here is some text and dummy text here', -22);
        $ruleChecker->getMatchingSubCategory($transaction);
    }

    /**
     * Multiple matches but for the same category.
     */
    public function testExceptionIsNotThrown(): void
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

        $this->assertEquals($ruleChecker->getMatchingSubCategory($transaction), $this->createSubCategory());
    }

    public function testPriorityMatters(): void
    {
        $rule1 = new SubCategoryTransactionRule();
        $rule1
            ->setContains('dummy text')
            ->setSubCategory($this->createSubCategory())
        ;
        $rule2 = new SubCategoryTransactionRule();
        $rule2
            ->setContains('some text')
            ->setSubCategory($this->createSubCategory('Test sub category expense 2'))
            ->setPriority(1)
        ;

        $this->mockSubCategoryTransactionRuleRepository([$rule1, $rule2]);
        $ruleChecker = new RuleChecker($this->ruleRepository);
        $transaction = $this->createTransaction('Here is some text and dummy text here', -22);

        $this->assertEquals($ruleChecker->getMatchingSubCategory($transaction), $rule2->getSubCategory());
    }
}
