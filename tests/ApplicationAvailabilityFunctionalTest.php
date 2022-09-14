<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function urlProvider()
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
        yield ['/transaction/categorize'];
        yield ['/transaction/export/elasticsearch'];
        yield ['/transaction/import/boursorama/upload'];
        yield ['/transaction/new'];
    }
}
