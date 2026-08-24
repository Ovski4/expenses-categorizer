<?php

namespace App\Tests\Services;

use App\Entity\Account;
use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Exception\InvalidSubCategoryAssignmentException;
use App\Services\TransactionDiffChecker;
use App\Services\TransactionSubCategoryAssigner;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionSubCategoryAssignerTest extends TestCase
{
    private function createSubCategory(string $name, string $transactionType): SubCategory
    {
        $topCategory = (new TopCategory())
            ->setName('Top '.$name)
            ->setTransactionType($transactionType);

        return (new SubCategory())->setName($name)->setTopCategory($topCategory);
    }

    private function createTransaction(float $amount): Transaction
    {
        return (new Transaction())
            ->setAccount((new Account())->setName('Fake account')->setCurrency('EUR'))
            ->setLabel('Fake transaction')
            ->setAmount($amount)
            ->setCreatedAt(new \DateTime('now'));
    }

    private function createAssigner(
        bool $subCategoryChanged,
        ConstraintViolationList $violations,
        int $expectedFlushCount,
    ): TransactionSubCategoryAssigner {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly($expectedFlushCount))->method('flush');

        $diffChecker = $this->createMock(TransactionDiffChecker::class);
        $diffChecker->method('subCategoryChanged')->willReturn($subCategoryChanged);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn($violations);

        return new TransactionSubCategoryAssigner($entityManager, $diffChecker, $validator);
    }

    public function testAssigningToAnUncategorizedTransactionMarksItManual(): void
    {
        $transaction = $this->createTransaction(-42.0);
        $subCategory = $this->createSubCategory('Groceries', TransactionType::EXPENSES);

        $assigner = $this->createAssigner(true, new ConstraintViolationList(), 1);
        $assigner->assign($transaction, $subCategory);

        $this->assertSame($subCategory, $transaction->getSubCategory());
        $this->assertTrue($transaction->isCategorizedManually());
    }

    public function testAssigningTheSameSubCategoryLeavesTheManualFlagAlone(): void
    {
        $subCategory = $this->createSubCategory('Groceries', TransactionType::EXPENSES);
        $transaction = $this->createTransaction(-42.0);
        $transaction->setSubCategory($subCategory);

        // The diff checker reports no change: a rule-assigned category re-picked as
        // is must not silently pin the transaction against future rule runs.
        $assigner = $this->createAssigner(false, new ConstraintViolationList(), 1);
        $assigner->assign($transaction, $subCategory);

        $this->assertFalse($transaction->isCategorizedManually());
    }

    public function testAViolationPreventsTheFlush(): void
    {
        $transaction = $this->createTransaction(-42.0);
        $subCategory = $this->createSubCategory('Salary', TransactionType::REVENUES);

        $violations = new ConstraintViolationList([
            new ConstraintViolation('transaction_sub_category.is_logical', null, [], null, null, null),
        ]);

        // Zero flushes: Transaction::checkSubCategory() throws a raw exception in a
        // PreUpdate callback, so an invalid pair must never reach the database.
        $assigner = $this->createAssigner(true, $violations, 0);

        $this->expectException(InvalidSubCategoryAssignmentException::class);
        $this->expectExceptionMessage('transaction_sub_category.is_logical');

        $assigner->assign($transaction, $subCategory);
    }
}
