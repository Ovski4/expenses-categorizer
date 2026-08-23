<?php

namespace App\Tests\Controller;

use App\Entity\Account;
use App\Entity\SubCategory;
use App\Entity\TopCategory;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Assigning a sub category from the transaction list.
 */
class TransactionSubCategoryTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $manager;

    private SubCategory $groceries;

    private SubCategory $rent;

    private SubCategory $salary;

    private Transaction $expense;

    private Transaction $categorized;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->manager = static::getContainer()->get(EntityManagerInterface::class);

        $account = (new Account())->setName('Test account')->setCurrency('EUR');
        $this->manager->persist($account);

        $expenses = (new TopCategory())
            ->setName('Everyday')
            ->setTransactionType(TransactionType::EXPENSES);
        $revenues = (new TopCategory())
            ->setName('Work')
            ->setTransactionType(TransactionType::REVENUES);
        $this->manager->persist($expenses);
        $this->manager->persist($revenues);

        $this->groceries = (new SubCategory())->setName('Groceries')->setTopCategory($expenses);
        $this->rent = (new SubCategory())->setName('Rent')->setTopCategory($expenses);
        $this->salary = (new SubCategory())->setName('Salary')->setTopCategory($revenues);
        $this->manager->persist($this->groceries);
        $this->manager->persist($this->rent);
        $this->manager->persist($this->salary);

        $this->expense = $this->createTransaction($account, 'Biocoop', -42.1);
        // Only needed so the list has a Revenues row to scope options against.
        $this->createTransaction($account, 'Payslip', 2400.0);
        $this->categorized = $this->createTransaction($account, 'Already done', -10.0);
        $this->categorized->setSubCategory($this->rent);

        $this->manager->flush();
    }

    private function createTransaction(Account $account, string $label, float $amount): Transaction
    {
        $transaction = (new Transaction())
            ->setAccount($account)
            ->setLabel($label)
            ->setAmount($amount)
            ->setCreatedAt(new \DateTime('2025-04-02'));

        $this->manager->persist($transaction);

        return $transaction;
    }

    private function rowOf(Crawler $crawler, string $label): Crawler
    {
        $row = $crawler->filter('#transaction-list tbody tr')->reduce(
            fn (Crawler $tr) => str_contains($tr->filter('td')->first()->text(), $label)
        );

        $this->assertCount(1, $row, sprintf('Expected exactly one row for "%s"', $label));

        return $row;
    }

    private function url(Transaction $transaction): string
    {
        return sprintf('/transaction/%s/sub-category', $transaction->getId());
    }

    /**
     * @param array<string, string> $parameters
     */
    private function patch(Transaction $transaction, array $parameters, bool $json = false): void
    {
        $this->client->request(
            'POST',
            $this->url($transaction),
            array_merge(['_method' => 'PATCH'], $parameters),
            [],
            $json ? ['HTTP_ACCEPT' => 'application/json'] : []
        );
    }

    private function token(Transaction $transaction): string
    {
        $crawler = $this->client->request('GET', '/transaction/');

        return $this->rowOf($crawler, (string) $transaction->getLabel())
            ->filter('input[name="_token"]')
            ->attr('value');
    }

    private function refresh(Transaction $transaction): Transaction
    {
        $this->manager->clear();

        return $this->manager->getRepository(Transaction::class)->find($transaction->getId());
    }

    public function testUncategorizedRowRendersASelect(): void
    {
        $crawler = $this->client->request('GET', '/transaction/');
        $this->assertResponseIsSuccessful();

        $row = $this->rowOf($crawler, 'Biocoop');
        $this->assertCount(1, $row->filter('form.inline-sub-category select[name="subCategory"]'));
        $this->assertStringContainsString('uncategorized-transaction', (string) $row->attr('class'));
    }

    public function testCategorizedRowRendersPlainTextAndNoSelect(): void
    {
        $crawler = $this->client->request('GET', '/transaction/');

        $row = $this->rowOf($crawler, 'Already done');
        $this->assertCount(0, $row->filter('select[name="subCategory"]'));
        $this->assertSame('Rent', $row->filter('td.sub-category-cell')->text());
        $this->assertStringNotContainsString('uncategorized-transaction', (string) $row->attr('class'));
    }

    public function testOptionsAreScopedToTheTransactionType(): void
    {
        $crawler = $this->client->request('GET', '/transaction/');

        $expenseOptions = $this->rowOf($crawler, 'Biocoop')->filter('option')->each(
            fn (Crawler $option) => $option->text()
        );
        $this->assertContains('Groceries', $expenseOptions);
        $this->assertContains('Rent', $expenseOptions);
        $this->assertNotContains('Salary', $expenseOptions);

        $revenueOptions = $this->rowOf($crawler, 'Payslip')->filter('option')->each(
            fn (Crawler $option) => $option->text()
        );
        $this->assertContains('Salary', $revenueOptions);
        $this->assertNotContains('Groceries', $revenueOptions);
    }

    public function testOptionsAreGroupedByTopCategory(): void
    {
        $crawler = $this->client->request('GET', '/transaction/');

        $this->assertCount(
            1,
            $this->rowOf($crawler, 'Biocoop')->filter('optgroup[label="Everyday"]')
        );
    }

    public function testSubmittingTheFormAssignsTheSubCategory(): void
    {
        $this->patch($this->expense, [
            '_token' => $this->token($this->expense),
            'subCategory' => (string) $this->groceries->getId(),
        ]);

        $this->assertResponseRedirects('/transaction/');

        $transaction = $this->refresh($this->expense);
        $this->assertSame('Groceries', (string) $transaction->getSubCategory());
        $this->assertTrue($transaction->isCategorizedManually());
    }

    public function testAssignedRowThenRendersLikeACategorizedRow(): void
    {
        $this->patch($this->expense, [
            '_token' => $this->token($this->expense),
            'subCategory' => (string) $this->groceries->getId(),
        ]);

        $crawler = $this->client->request('GET', '/transaction/');
        $row = $this->rowOf($crawler, 'Biocoop');

        // Exactly what testCategorizedRowRendersPlainTextAndNoSelect asserts about a
        // row that was already categorized when the page was rendered.
        $this->assertCount(0, $row->filter('select[name="subCategory"]'));
        $this->assertSame('Groceries', $row->filter('td.sub-category-cell')->text());
        $this->assertStringNotContainsString('uncategorized-transaction', (string) $row->attr('class'));
    }

    public function testFallbackRedirectKeepsFiltersAndPage(): void
    {
        $token = $this->token($this->expense);
        $listUrl = '/transaction/?item_filter%5Blabel%5D=Biocoop&page=1';

        $this->client->request('GET', $listUrl);
        $this->client->request(
            'POST',
            $this->url($this->expense),
            [
                '_method' => 'PATCH',
                '_token' => $token,
                'subCategory' => (string) $this->groceries->getId(),
            ],
            [],
            ['HTTP_REFERER' => 'http://localhost'.$listUrl]
        );

        $this->assertResponseRedirects($listUrl);
    }

    public function testJsonRequestReturnsTheSubCategory(): void
    {
        $this->patch($this->expense, [
            '_token' => $this->token($this->expense),
            'subCategory' => (string) $this->groceries->getId(),
        ], true);

        $this->assertResponseIsSuccessful();
        $this->assertSame(
            [
                'id' => (string) $this->expense->getId(),
                'subCategory' => [
                    'id' => (string) $this->groceries->getId(),
                    'name' => 'Groceries',
                ],
            ],
            json_decode((string) $this->client->getResponse()->getContent(), true)
        );
    }

    public function testAnInvalidCsrfTokenIsRejected(): void
    {
        $this->patch($this->expense, [
            '_token' => 'not-a-valid-token',
            'subCategory' => (string) $this->groceries->getId(),
        ], true);

        $this->assertResponseStatusCodeSame(403);
        $this->assertNull($this->refresh($this->expense)->getSubCategory());
    }

    public function testACrossTypeSubCategoryIsRejected(): void
    {
        // Salary is a Revenues category, the transaction is an expense.
        $this->patch($this->expense, [
            '_token' => $this->token($this->expense),
            'subCategory' => (string) $this->salary->getId(),
        ], true);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString(
            'does not make sense',
            (string) $this->client->getResponse()->getContent()
        );
        $this->assertNull($this->refresh($this->expense)->getSubCategory());
    }

    public function testAnUnknownSubCategoryIsRejected(): void
    {
        $this->patch($this->expense, [
            '_token' => $this->token($this->expense),
            'subCategory' => '3d7c2a1e-0000-4000-8000-000000000000',
        ], true);

        $this->assertResponseStatusCodeSame(422);
        $this->assertNull($this->refresh($this->expense)->getSubCategory());
    }

    public function testAnEmptySubCategoryIsRejectedAndDoesNotClear(): void
    {
        // The token is read while the row still renders a form, then reused: it is
        // tied to the transaction id, not to the transaction being uncategorized.
        $token = $this->token($this->expense);

        $this->patch($this->expense, [
            '_token' => $token,
            'subCategory' => (string) $this->groceries->getId(),
        ], true);
        $this->assertResponseIsSuccessful();

        $this->patch($this->expense, ['_token' => $token, 'subCategory' => ''], true);

        $this->assertResponseStatusCodeSame(422);
        // The endpoint is set-only: the category just assigned is untouched.
        $this->assertSame('Groceries', (string) $this->refresh($this->expense)->getSubCategory());
    }

    public function testAnUnknownTransactionIsNotFound(): void
    {
        $this->client->request('POST', '/transaction/3d7c2a1e-0000-4000-8000-000000000000/sub-category', [
            '_method' => 'PATCH',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}
