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
        yield ['/'];
        yield ['/sub/category/'];
        yield ['/sub/category/new'];
        yield ['/sub/category/transaction/rule/'];
        yield ['/sub/category/transaction/rule/new'];
        yield ['/top/category/'];
        yield ['/top/category/new'];
        yield ['/transaction/'];
        yield ['/transaction/new'];
        yield ['/transaction/?page=1&only_show_uncategorized=true'];
    }
}
