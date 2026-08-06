<?php

namespace App\Tests\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TransactionImportControllerTest extends WebTestCase
{
    private const ACCOUNT_NAME = 'BAPTISTE PIERRE JOSEPH BOUCHEREAU N26';
    private const STATEMENT = 'n26-statement.pdf';

    /**
     * What the account statement parser api returns for the n26 statement.
     */
    private const PARSER_API_RESULTS = [
        [
            'account' => self::ACCOUNT_NAME,
            'date' => '02/04/2025',
            'label' => 'OVH SAS',
            'value' => -34.3,
        ],
        [
            'account' => self::ACCOUNT_NAME,
            'date' => '12/04/2025',
            'label' => 'CAMPUS.COACH',
            'value' => -15.0,
        ],
        [
            'account' => self::ACCOUNT_NAME,
            'date' => '12/04/2025',
            'label' => 'CCM LOIRE DIVATTE - Date de valeur 12.04.2025',
            'value' => -20.0,
        ],
    ];

    private KernelBrowser $client;

    private EntityManagerInterface $manager;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
        $this->account = $this->createAccount();
        $this->mockParserApiResults(self::PARSER_API_RESULTS);
    }

    public function testTransactionsToImportAreListed(): void
    {
        $crawler = $this->client->request('GET', $this->getValidateTransactionsUrl());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Validate transactions');
        $this->assertSelectorTextContains('.alert-info', '3 transactions to import');
        $this->assertSelectorTextContains('.alert-info', 'total amount is -69.3');
        $this->assertSelectorTextContains('.alert-info', self::ACCOUNT_NAME);
        // Nothing imported yet, so the new balance is the total amount
        $this->assertSelectorTextContains('.expected-amount', '-69.3');
        $this->assertSelectorNotExists('.alert-warning');
        $this->assertSelectorNotExists('.alert-danger');

        $rows = $crawler->filter('tbody tr');
        $this->assertCount(3, $rows);
        $this->assertRowMatches($rows->eq(0), 'OVH SAS', '-34.3', '02/04/2025');
        $this->assertRowMatches($rows->eq(1), 'CAMPUS.COACH', '-15', '12/04/2025');
        $this->assertRowMatches($rows->eq(2), 'CCM LOIRE DIVATTE', '-20', '12/04/2025');
    }

    public function testTransactionsToImportAreListedWithoutAccountInTheUrl(): void
    {
        // What the n26 upload form redirects to: the parser reads the account
        // from the statement, so no account is passed in the url.
        $crawler = $this->client->request(
            'GET',
            '/transaction/import/validate-transactions/n26/'.self::STATEMENT
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-info', '3 transactions to import');
        $this->assertSelectorTextContains('.alert-info', self::ACCOUNT_NAME);
        $this->assertSelectorTextContains('.expected-amount', '-69.3');
        $this->assertCount(3, $crawler->filter('tbody tr'));
    }

    public function testAlreadyImportedTransactionsAreFlagged(): void
    {
        $this->createTransaction('OVH SAS', -34.3, '02/04/2025');

        $crawler = $this->client->request('GET', $this->getValidateTransactionsUrl());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-warning', '1 of these transactions already exists!');
        // The already imported transaction is part of the account balance
        $this->assertSelectorTextContains('.expected-amount', '-103.6');
        $this->assertCount(1, $crawler->filter('tbody tr.existing-transaction'));
        $this->assertCount(2, $crawler->filter('tbody tr.new-transaction'));
    }

    public function testTransactionsAreSaved(): void
    {
        $this->client->request('POST', $this->getValidateTransactionsUrl());

        $this->assertResponseRedirects('/transaction/');

        $transactions = $this->findAccountTransactions();
        $this->assertCount(3, $transactions);
        $this->assertSame(
            [
                ['OVH SAS', -34.3, '02/04/2025'],
                ['CAMPUS.COACH', -15.0, '12/04/2025'],
                ['CCM LOIRE DIVATTE - Date de valeur 12.04.2025', -20.0, '12/04/2025'],
            ],
            array_map(
                fn (Transaction $transaction) => [
                    $transaction->getLabel(),
                    $transaction->getAmount(),
                    $transaction->getCreatedAt()->format('d/m/Y'),
                ],
                $transactions
            )
        );
    }

    public function testOnlyNewTransactionsAreSavedWhenAsked(): void
    {
        $this->createTransaction('OVH SAS', -34.3, '02/04/2025');

        $this->client->request('POST', $this->getValidateTransactionsUrl(), [
            'saveOnlyNewTransactions' => 'true',
        ]);

        $this->assertResponseRedirects('/transaction/');

        // The already existing transaction has not been imported twice
        $this->assertCount(3, $this->findAccountTransactions());
    }

    public function testAllTransactionsAreSavedWhenDuplicatesAreAccepted(): void
    {
        $this->createTransaction('OVH SAS', -34.3, '02/04/2025');

        $this->client->request('POST', $this->getValidateTransactionsUrl());

        $this->assertResponseRedirects('/transaction/');

        // The already existing transaction has been imported a second time
        $this->assertCount(4, $this->findAccountTransactions());
    }

    public function testErrorIsDisplayedWhenNoTransactionIsFound(): void
    {
        $this->mockParserApiResults([]);

        $crawler = $this->client->request('GET', $this->getValidateTransactionsUrl());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            '.alert-danger',
            'No transactions were found. Are you sure your file is a valid n26 account statement?'
        );
        $this->assertSelectorTextContains('.alert-info a', 'Go back to file upload');
        $this->assertSame(
            '/transaction/import/n26/upload',
            parse_url($crawler->filter('.alert-info a')->attr('href'), PHP_URL_PATH)
        );
    }

    public function testErrorIsDisplayedWhenTheAccountDoesNotExist(): void
    {
        $results = array_map(
            fn (array $result) => array_replace($result, ['account' => 'UNKNOWN ACCOUNT']),
            self::PARSER_API_RESULTS
        );
        $this->mockParserApiResults($results);

        // No account in the url: the n26 parser reads it from the statement
        $crawler = $this->client->request(
            'GET',
            '/transaction/import/validate-transactions/n26/'.self::STATEMENT
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'UNKNOWN ACCOUNT');
        $this->assertSelectorTextContains(
            '.alert-danger',
            'You need to create an account with this name or alias before importing transactions'
        );
        $this->assertSelectorTextContains('.alert-info a', 'Create an account now');
        $this->assertSame(
            '/account/new',
            parse_url($crawler->filter('.alert-info a')->attr('href'), PHP_URL_PATH)
        );
    }

    /**
     * The url the upload form redirects to for parsers that do not extract the
     * account from the statement (the account is then part of the url).
     */
    private function getValidateTransactionsUrl(): string
    {
        return sprintf(
            '/transaction/import/validate-transactions/n26/%s?account=%s',
            self::STATEMENT,
            $this->account->getId()
        );
    }

    /**
     * Make the account statement parser api return the given results,
     * instead of actually calling it over http.
     */
    private function mockParserApiResults(array $results): void
    {
        $mockHttpClient = static::getContainer()->get(MockHttpClient::class);
        $mockHttpClient->setResponseFactory(function (string $method, string $url) use ($results) {
            $this->assertSame('GET', $method);
            $this->assertSame(
                'http://account_statement_parser/n26?statement=/var/statements/'.self::STATEMENT,
                $url
            );

            return new MockResponse(json_encode($results));
        });
    }

    /**
     * Accounts are looked up by name or alias, so any account already stored
     * with the statement account name would make the lookup ambiguous. Move
     * them out of the way (rolled back at the end of the test).
     */
    private function isolateStatementAccountName(): void
    {
        $accounts = $this->manager->getRepository(Account::class)
            ->createQueryBuilder('a')
            ->where('a.aliases LIKE :alias')
            ->orWhere('a.name = :name')
            ->setParameter('alias', '%'.self::ACCOUNT_NAME.'%')
            ->setParameter('name', self::ACCOUNT_NAME)
            ->getQuery()
            ->getResult()
        ;

        foreach ($accounts as $account) {
            $account
                ->setName(uniqid('Account renamed by test '))
                ->setAliases([])
            ;
        }

        $this->manager->flush();
    }

    private function createAccount(): Account
    {
        $this->isolateStatementAccountName();

        $account = (new Account())
            ->setName(self::ACCOUNT_NAME)
            ->setCurrency('EUR')
        ;

        $this->manager->persist($account);
        $this->manager->flush();

        return $account;
    }

    private function createTransaction(string $label, float $amount, string $date): Transaction
    {
        $transaction = (new Transaction())
            ->setLabel($label)
            ->setAmount($amount)
            ->setCreatedAt(\DateTime::createFromFormat('d/m/Y', $date)->setTime(0, 0, 0))
            ->setAccount($this->account)
        ;

        $this->manager->persist($transaction);
        $this->manager->flush();

        return $transaction;
    }

    /**
     * @return Transaction[]
     */
    private function findAccountTransactions(): array
    {
        $accountId = $this->account->getId();
        $this->manager->clear();

        return $this->manager->getRepository(Transaction::class)->findBy(
            ['account' => $accountId],
            ['createdAt' => 'ASC', 'amount' => 'DESC']
        );
    }

    private function assertRowMatches(Crawler $row, string $label, string $amount, string $date): void
    {
        $cells = $row->filter('td');

        $this->assertStringContainsStringIgnoringCase($label, $cells->eq(0)->text());
        $this->assertSame($amount, $cells->eq(1)->text());
        $this->assertSame($date, $cells->eq(2)->text());
        $this->assertSame(self::ACCOUNT_NAME, $cells->eq(3)->text());
    }
}
