<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful(string $url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return iterable<int, array{0: string}>
     */
    public function urlProvider(): iterable
    {
        yield ['/transaction/new'];
        yield ['/transaction/?page=1&only_show_uncategorized=true'];
        yield ['/account/'];
        yield ['/account/new'];
        yield ['/category/'];
        yield ['/sub/category/new'];
        yield ['/top/category/new'];
        yield ['/sub/category/transaction/rule/'];
        yield ['/sub/category/transaction/rule/new'];
        yield ['/transaction/'];
        // Submitted filter forms: the only way the apply_filter closures run
        yield ['/transaction/?item_filter[label]=OVH'];
        yield ['/transaction/?item_filter[categorized]=y'];
        yield ['/transaction/?item_filter[categorized]=n'];
        yield ['/transaction/?item_filter[categorized]='];
        yield ['/sub/category/transaction/rule/?item_filter[contains]=x'];
        yield ['/transaction/categorize'];
        yield ['/transaction/export/elasticsearch'];
        yield ['/transaction/import/boursorama/upload'];
        yield ['/transaction/new'];
    }
}
